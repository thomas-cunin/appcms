<script>
    import {onMount} from 'svelte';
    import {Editor} from '@tiptap/core';
    import ToolbarButton from "./components/toolbar/ToolbarButton.svelte";
    import StarterKit from '@tiptap/starter-kit';
    import Focus from '@tiptap/extension-focus'

    import Image from '@tiptap/extension-image';
    import {Color} from '@tiptap/extension-color';
    import TextAlign from '@tiptap/extension-text-align';
    import FontFamily from '@tiptap/extension-font-family';
    import {TextStyle} from '@tiptap/extension-text-style';
    import BubbleMenu from '@tiptap/extension-bubble-menu'
    // import {TaskList} from '@tiptap/extension-task-list';
    // import {TaskItem} from '@tiptap/extension-task-item';
    import {writable} from 'svelte/store';
    import Coloris from "@melloware/coloris";
    import Swal from 'sweetalert2';
    import FloatingMenu from '@tiptap/extension-floating-menu';

    const CustomImage = Image.extend({
        addAttributes() {
            return {
                ...this.parent?.(),
                width: {
                    default: null,
                    parseHTML: element => element.style.width || element.getAttribute('width') || null,
                    renderHTML: attributes => {
                        const {width, ...attrs} = attributes;
                        if (width) {
                            attrs.style = `width: ${width}`;
                        }
                        return attrs;
                    },
                },
            };
        },
    });
    let editor;
    let editorContainer;
    let imageBubbleMenuContainer;
    let inlineToolsBubbleMenuContainer;
    let floatingAddMenuContainer; // Add this to your script section


    const isBold = writable(false);
    const isItalic = writable(false);
    const isStrike = writable(false);
    const isParagraph = writable(false);
    const isTextAlignLeft = writable(false);
    const isTextAlignCenter = writable(false);
    const isTextAlignRight = writable(false);
    const isTextAlignJustify = writable(false);
    const isText = writable(false);
    const isHeading1 = writable(false);
    const isHeading2 = writable(false);
    const isHeading3 = writable(false);
    const isBulletList = writable(false);
    const isOrderedList = writable(false);
    const isBlockquote = writable(false);
    const currentColor = writable('#000000');
    const currentFontFamily = writable(false);
    const currentStyle = writable('P'); // Default to Paragraph
    const mediaLibraryUrl = writable('');
    let localColor = '#000000';

    onMount(() => {


        editor = new Editor({
            element: editorContainer,
            extensions: [
                StarterKit,
                CustomImage,
                TextStyle,
                Color,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                FontFamily,
                Focus.configure({
                    className: 'has-focus',
                    mode: 'all',
                }),
                BubbleMenu.configure({
                    element: imageBubbleMenuContainer, // Attach the bubble menu to this element
                    pluginKey: 'image',
                    tippyOptions: {
                        placement: 'top',
                    },
                    shouldShow: ({editor, state, node}) => {
                        return editor.isActive('image'); // Show only if an image is selected
                    },
                }),
                BubbleMenu.configure({
                    element: inlineToolsBubbleMenuContainer, // Attach the bubble menu to this element
                    pluginKey: 'inline-tools',
                    tippyOptions: {
                        duration: 100,
                    },
                    shouldShow: ({editor, state, node}) => {
                        // Show the bubble menu if slelection is not empty
                        const {from, to} = state.selection;
                        return (to - from >= 1) && (editor.isActive('paragraph') || editor.isActive('heading'));
                    },
                }),
                FloatingMenu.configure({
                    element: floatingAddMenuContainer,
                    shouldShow: null,
                    tippyOptions: {
                        placement: 'left',
                    },
                }),
            ],
            content: '',
            onUpdate: () => {
                document.querySelector('#content_page_content').value = editor.getHTML();
                updateButtonStates();
            },
            onTransaction: updateButtonStates,
        });


        Coloris.init();
        Coloris({
            el: '.coloris',
            swatches: [
                '#264653',
                '#2a9d8f',
                '#e9c46a',
                '#f4a261',
                '#e76f51',
                '#d62828',
                '#023e8a',
                '#0077b6',
                '#0096c7',
                '#00b4d8',
                '#48cae4'
            ]
        });
        Coloris.setInstance('#color-picker-input', {
            theme: 'polaroid',
            swatchesOnly: true,
            wrap: false,
        });
        const editorEl = document.querySelector('#editor');
        if (editorEl !== undefined && editorEl.getAttribute('data-media-library-url') !== null) {
            mediaLibraryUrl.set(editorEl.getAttribute('data-media-library-url'));
            console.log('Media Library URL', mediaLibraryUrl);
        } else {
            console.error('No media library URL provided', editorEl);
        }
        editor.commands.setContent(document.querySelector('#content_page_content').value);
    });

    function updateTextArea() {
        // document.querySelector('#content_page_editor_content').value = editor.getHTML();
    }

    function updateButtonStates() {
        updateTextArea();
        isBold.set(editor.isActive('bold'));
        isItalic.set(editor.isActive('italic'));
        isStrike.set(editor.isActive('strike'));
        isParagraph.set(editor.isActive('paragraph'));
        isTextAlignLeft.set(editor.isActive({textAlign: 'left'}));
        isTextAlignCenter.set(editor.isActive({textAlign: 'center'}));
        isTextAlignRight.set(editor.isActive({textAlign: 'right'}));
        isText.set(editor.isActive('text'));
        isHeading1.set(editor.isActive('heading', {level: 1}));
        isHeading2.set(editor.isActive('heading', {level: 2}));
        isHeading3.set(editor.isActive('heading', {level: 3}));

        isBulletList.set(editor.isActive('bulletList'));
        isOrderedList.set(editor.isActive('orderedList'));
        isBlockquote.set(editor.isActive('blockquote'));

        const attrs = editor.getAttributes('textStyle');
        currentColor.set(attrs.color || '#000000');
        localColor = attrs.color || '#000000';
        currentFontFamily.set(attrs.fontFamily);

        if (editor.isActive('heading', {level: 1})) {
            currentStyle.set('H1');
        } else if (editor.isActive('heading', {level: 2})) {
            currentStyle.set('H2');
        } else if (editor.isActive('heading', {level: 3})) {
            currentStyle.set('H3');
        } else {
            currentStyle.set('P');
        }
    }

    function setImageSize(size) {
        editor.chain().focus().updateAttributes('image', {width: size}).run();
    }

    function toggleBold() {
        editor.chain().focus().toggleBold().run();
    }

    function toggleItalic() {
        editor.chain().focus().toggleItalic().run();
    }

    function toggleStrike() {
        editor.chain().focus().toggleStrike().run();
    }

    function toggleTextAlignLeft() {
        editor.chain().focus().setTextAlign('left').run();
    }

    function toggleTextAlignCenter() {
        editor.chain().focus().setTextAlign('center').run();
    }

    function toggleTextAlignRight() {
        editor.chain().focus().setTextAlign('right').run();
    }

    function insertHeading(level) {
        editor.chain().focus().toggleHeading({level}).run();
    }

    function insertImage() {
        // editor.chain().focus().setImage({ src: url, width: '100%' }).run();
        Swal.fire({
            title: 'Insert Image',
            input: 'url',
            placeholder: 'Enter the URL of the image',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Insert',
            showLoaderOnConfirm: true,
            preConfirm: (url) => {
                return editor.chain().focus().setImage({src: url, width: '100%'}).run();
            },
        });
    }

    function insertImageFromMediaLibrary() {
        // get html content with fetch from media library url and display html in swal big modal
        Swal.fire({
            title: 'Insert Image from Media Library',
            html: '<div class="w" id="media-library-content"></div>',
            showCancelButton: false,
            showConfirmButton: false,
            showCloseButton: true,
            //with bulma class make a full width modal
            customClass: {
                popup: 'is-fullwidth media-library-popup'
            },
            showLoaderOnConfirm: true,
            didOpen: () => {
                console.log('Media Library URL', $mediaLibraryUrl);
                Swal.showLoading();
                fetch($mediaLibraryUrl)
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('#media-library-content').innerHTML = data;
                        document.querySelectorAll('#media-library-content div[data-media-url]').forEach((img) => {
                            img.addEventListener('click', (event) => {
                                const url = event.target.closest('div[data-media-url]').getAttribute('data-media-url');
                                editor.chain().focus().setImage({src: url, width: '100%'}).run();
                                Swal.close();
                            });
                        });
                        Swal.hideLoading();

                    });
            }
        });
    }

    function insertBulletList() {
        editor.chain().focus().toggleBulletList().run();
    }

    function insertOrderedList() {
        editor.chain().focus().toggleOrderedList().run();
    }

    function insertHorizontalRule() {
        editor.chain().focus().setHorizontalRule().run();
    }

    function insertBlockquote() {
        editor.chain().focus().toggleBlockquote().run();
    }

    function setParagraph() {
        editor.chain().focus().setParagraph().run();
    }

    function setColor(event) {
        const color = event.target.value;
        currentColor.set(color);
        editor.chain().focus().setColor(color).run();
    }

    function handleColorIconClick(event) {
        event.preventDefault();
        document.querySelector('#color-picker-input').click();
    }

    function getCurrentSelectionTypeIcon() {
        if ($isHeading1) {
            return 'ri-h-1';
        }
        if ($isHeading2) {
            return 'ri-h-2';
        }
        if ($isHeading3) {
            return 'ri-h-3';
        }
        if ($isBulletList) {
            return 'ri-list-unordered';
        }
        if ($isOrderedList) {
            return 'ri-list-ordered-2';
        }
    }

    function handleDropdownClick(event, action) {
        event.preventDefault();
        action();
    }

    const headingTexts = writable({
        H1: 'Titre 1',
        H2: 'Titre 2',
        H3: 'Titre 3',
        P: 'Paragraphe',
    })
</script>

<div>
    <div bind:this={floatingAddMenuContainer} class="floating-add-menu" style="visibility: hidden;">
        <div class="dropdown is-hoverable">
            <div class="dropdown-trigger">
                <button class="button is-small is-square mr-2" on:click|preventDefault>
        <span class="icon">
          <i class="ri-add-line"></i>
        </span>
                </button>
            </div>

            <div class="dropdown-menu" role="menu">
                <div class="dropdown-content">
                <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertHeading(1)}}>
          <span class="icon">
            <i class="ri-h-1"></i>
          </span>
                    <span>Heading 1</span>
                </a>
                <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertHeading(2)}}>
            <span class="icon">
                <i class="ri-h-2"></i>
            </span>
                    <span>Heading 2</span>
                </a>
                <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertHeading(3)}}>
            <span class="icon">
                <i class="ri-h-3"></i>
            </span>
                    <span>Heading 3</span>
                </a>
                <hr class="dropdown-divider"/>
                <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertBulletList()}}>
          <span class="icon">
            <i class="ri-list-unordered"></i>
          </span>
                    <span>Add List</span>
                </a>
                <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertOrderedList()}}>
            <span class="icon">
                <i class="ri-list-ordered-2"></i>
            </span>
                    <span>Add Ordered List</span>
                </a>
                <hr class="dropdown-divider"/>
                    <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertImage()}}>
          <span class="icon">
            <i class="ri-image-line"></i>
          </span>
                        <span>Add Image from url</span>
                    </a>
                    <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertImageFromMediaLibrary()}}>
          <span class="icon">
            <i class="ri-image-line"></i>
          </span>
                        <span>Add Image</span>
                    </a>
                    <hr class="dropdown-divider"/>
                    <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertHorizontalRule()}}>
            <span class="icon">
                <i class="ri-sep-line"></i>
            </span>
                        <span>Add Horizontal Rule</span>
                    </a>
                    <hr class="dropdown-divider"/>

                    <a href="#" class="dropdown-item" on:click|preventDefault={()=>{insertBlockquote()}}>
          <span class="icon">
            <i class="ri-quote-text"></i>
          </span>
                        <span>Add Quote</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="floating-menu" bind:this={inlineToolsBubbleMenuContainer}>
        <div class="toolbar">
            <ToolbarButton active={$isBold} onClick={toggleBold} icon={'ri-bold'} label="B"/>
            <ToolbarButton active={$isItalic} onClick={toggleItalic} icon={'ri-italic'} label="I"/>
            <ToolbarButton active={$isStrike} onClick={toggleStrike} icon={'ri-strikethrough'} label="S"/>
            <ToolbarButton active={$isTextAlignLeft} onClick={toggleTextAlignLeft} icon={"ri-align-left"}/>
            <ToolbarButton active={$isTextAlignCenter} onClick={toggleTextAlignCenter} icon={"ri-align-center"}/>
            <ToolbarButton active={$isTextAlignRight} onClick={toggleTextAlignRight} icon={"ri-align-right"}/>
            <button class="tool" type="button" on:click|preventDefault={(e) => handleColorIconClick(e)}>
                <i class="ri-font-color" style="color: {$currentColor};"></i>
            </button>
                <input type="text" class="coloris" id="color-picker-input" value={$currentColor} on:change={setColor}/>
        </div>
    </div>
    <div bind:this={imageBubbleMenuContainer} class="bubble-menu">
                <button on:click={() => setImageSize('25%')}>25%</button>
                <button on:click={() => setImageSize('50%')}>50%</button>
                <button on:click={() => setImageSize('75%')}>75%</button>
                <button on:click={() => setImageSize('100%')}>100%</button>
<!--        <input class="slider is-fullwidth is-small is-circle" type="range" min="25" max="100" step="25" value="100"-->
<!--               on:change={(e) => setImageSize(e.target.value + '%')}/>-->
    </div>
    <div bind:this={editorContainer} class="editor"></div>
</div>

<style>

    .bubble-menu {
        display: flex;
        gap: 10px;
        background-color: white;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 4px;
    }

    .bubble-menu button {
        cursor: pointer;
        padding: 5px 10px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .bubble-menu button:hover {
        background-color: #e0e0e0;
    }

</style>