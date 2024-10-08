function updateMenuOrder(menuItemUUID, parentMenuUUID, positionIndex) {
    fetch(window.origin + '/manage/app/menu/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest', // Important for Symfony to recognize AJAX
        },
        body: JSON.stringify({
            menuItem: menuItemUUID,
            parentMenu: parentMenuUUID,
            positionIndex: positionIndex
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Menu order updated successfully.');
            } else {
                console.error('Error updating menu order:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

const initForm = (form, callback = undefined, cancelCallback = undefined) => {
    if (cancelCallback) {
        const cancelButton = form.querySelector('button[data-action="cancel_form"]');
        if (cancelButton) {
            cancelButton.addEventListener('click', cancelCallback);
        }
    }
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const url = form.getAttribute('action');
        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (callback) {
                        callback(data);
                    }
                } else {
                    // replace form by html from data.content
                    if (data.content) {
                        const parent = form.parentElement;
                        // replace form by html from data.content
                        parent.innerHTML = data.content;
                        initForm(parent.querySelector('form'), callback, cancelCallback);

                    }
                }

            })
            .catch(error => {

            });
    });
};

const updatePageElement = (elementUID, data) => {
    if (data.name) {
        let element;
        if (data.type === 'menu') {
            element = document.querySelector(`[data-menu-id="${elementUID}"]`);
        } else {
            element = document.querySelector(`[data-page-id="${elementUID}"]`);
        }
        const menuTitle = element.querySelector('.menu-title');
        const span = menuTitle.querySelector('span.page-name');
        span.textContent = data.name;
    }

};

// when click on a .menu-item, get closest .menu-item and load ajax content from data-edit-settings-url and put response.content in #page-settings
const initAppStructure = () => {

    document.querySelectorAll('.menu-item .dropdown-item[data-action="open_page_settings"]').forEach(function (editSettingsLink) {
        editSettingsLink.addEventListener('click', function (e) {
            e.preventDefault();
            const menuItem = editSettingsLink.closest('.menu-item');
            const editSettingsUrl = menuItem.getAttribute('data-edit-settings-url');
            const pageSettingsContainer = document.getElementById('page-settings');

            fetch(editSettingsUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        // show #page-settings
                        pageSettingsContainer.classList.remove('is-hidden');
                        pageSettingsContainer.innerHTML = data.content;
                        initForm(pageSettingsContainer.querySelector('form'), (data) => {
                            if (data.success) {
                                updatePageElement(data.updatedData.uuid, data.updatedData);
                                // hide #page-settings
                                pageSettingsContainer.classList.add('is-hidden');
                                document.getElementById('app-structure').classList.remove('is-hidden');
                            }
                        }, () => {
                            // make settings container empty
                            pageSettingsContainer.innerHTML = '';
                            // hide #page-settings
                            pageSettingsContainer.classList.add('is-hidden');
                            document.getElementById('app-structure').classList.remove('is-hidden');
                        });
                        // hide #app-structure
                        document.getElementById('app-structure').classList.add('is-hidden');
                    } else {
                        console.error('Invalid response format:', data);
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        });
    });

    document.querySelectorAll('.menu-item .dropdown-item[data-action="remove_page"]').forEach(function (removePageLink) {
        removePageLink.addEventListener('click', function (e) {
            e.preventDefault();
            const menuItem = removePageLink.closest('.menu-item');
            const removePageUrl = menuItem.getAttribute('data-remove-url');
            const pageSettingsContainer = document.getElementById('page-settings');

            Swal.fire({
                title: 'Êtes-vous sûr?',
                text: "Vous allez supprimer cet élément, cette action est irréversible!",
                html: `
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="keepChildren">
                            Conserver les éléments enfants
                        </label>
                    </div>
                </div>
            `,
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                preConfirm: () => {
                    const keepChildren = Swal.getPopup().querySelector('#keepChildren').checked;
                    return {keepChildren: keepChildren};
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('result', result);
                    const keepChildren = result.value.keepChildren;
                    fetch(removePageUrl, {
                        method: 'POST',
                        body: JSON.stringify({keepChildren: keepChildren}),
                        headers: {'Content-Type': 'application/json'},
                    }).then(response => {
                        if (response.ok) {
                            // Supprimer l'élément du DOM ou afficher un message de succès
                        } else {
                            // Gérer les erreurs
                        }
                    });
                }
            });
        });
    });


    // Sélectionne toutes les icônes de bascule
    const toggleIcons = document.querySelectorAll(".toggle-icon");

    toggleIcons.forEach(icon => {
        icon.addEventListener("click", function () {
            // Trouve le contenu du menu associé
            const menuItem = icon.closest(".menu-item");
            const menuContent = menuItem.querySelector(".menu-content");

            // Ouvre ou ferme le menu
            if (menuContent.classList.contains("open")) {
                menuContent.classList.remove("open");
                icon.querySelector("i").classList.replace("ri-arrow-up-s-line", "ri-arrow-down-s-line");
            } else {
                menuContent.classList.add("open");
                icon.querySelector("i").classList.replace("ri-arrow-down-s-line", "ri-arrow-up-s-line");
            }
        });
    });


    // Initialize sortable for menu items
    const menuItemsContainer = document.querySelectorAll('.menu-content');

    menuItemsContainer.forEach(container => {
        // don't accept drag of elment with no-draggable class
        new Sortable(container, {
            group: 'nested',
            animation: 150,
            ghostClass: 'sortable-ghost',
            filter: '.no-draggable',
            onMove: function (evt) {
                const draggedItem = evt.dragged;
                const relatedItem = evt.related;

                // Check if the dragged item is being placed after a non-draggable item
                const isAfterNoDraggable = relatedItem.classList.contains('no-draggable') && evt.willInsertAfter;

                // Prevent the move if trying to drop after a non-draggable item
                if (isAfterNoDraggable) {
                    return false;
                }

                // Allow the move otherwise
                return true;
            },
            onEnd: function (/**Event*/evt) {
                const itemEl = evt.item;  // the dragged item

                // Get necessary data attributes
                const menuItemId = itemEl.getAttribute('data-menu-item-id');
                const parentMenuId = itemEl.closest('.menu-content').closest('[data-menu-id]').getAttribute('data-menu-id');
                const newPositionIndex = Array.from(container.children).indexOf(itemEl);

                // Send AJAX request to update the backend
                updateMenuOrder(menuItemId, parentMenuId, newPositionIndex);
            },
        });
    });

    function refreshAppStructure() {
        const structureContainer = document.getElementById('app-structure');
        const url = structureContainer.getAttribute('data-refresh-url');

        fetch(url).then(response => response.json()).then(data => {
            if (data.content) {
                structureContainer.innerHTML = data.content;
                initAppStructure();
            } else {
                console.error('Invalid response format:', data);
            }
        })
    }

    // Add click event listeners to all elements with data-action="add-page"
    const addPageElements = document.querySelectorAll('[data-action="add-page"]');
    addPageElements.forEach(function (element) {
        element.addEventListener('click', function () {
            const url = element.getAttribute('data-action-url');
            const pageSettingsContainer = document.getElementById('page-settings');
            const structureContainer = document.getElementById('app-structure');
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        structureContainer.classList.add('is-hidden');
                        pageSettingsContainer.classList.remove('is-hidden');
                        pageSettingsContainer.innerHTML = data.content;
                        console.log(pageSettingsContainer.querySelectorAll('[data-action="get-add-page-form"]'))
                        pageSettingsContainer.querySelectorAll('[data-action="get-add-page-form"]').forEach(function (card) {
                            card.addEventListener('click', function () {
                                var action = card.getAttribute('data-action');
                                var actionUrl = card.getAttribute('data-action-url');
                                fetch(actionUrl)
                                    .then(response => response.json())
                                    .then(data => {
                                        console.log(data);
                                        if (data.content) {
                                            pageSettingsContainer.innerHTML = data.content;
                                            initForm(pageSettingsContainer.querySelector('form'), (data) => {
                                                if (data.success) {
                                                    refreshAppStructure();
                                                    pageSettingsContainer.classList.add('is-hidden');
                                                    structureContainer.classList.remove('is-hidden');
                                                }
                                            }, () => {
                                                pageSettingsContainer.innerHTML = '';
                                                pageSettingsContainer.classList.add('is-hidden');
                                                structureContainer.classList.remove('is-hidden');
                                            });
                                        }
                                    });
                            });
                        });
                    } else {
                        console.error('Invalid response format:', data);
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        });
    });
};

document.addEventListener('DOMContentLoaded', initAppStructure);