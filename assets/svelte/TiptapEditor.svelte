<script>
    import {onMount} from 'svelte';
    import {Editor} from '@tiptap/core';
    import ToolbarButton from "./components/toolbar/ToolbarButton.svelte";
    import StarterKit from '@tiptap/starter-kit';
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


    let editor;
    let editorContainer;
    let bubbleMenuContainer;

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

    let localColor = '#000000';

    onMount(() => {
        editor = new Editor({
            element: editorContainer,
            extensions: [
                StarterKit,
                Image.configure({
                    inline: true,
                }),

                TextStyle,
                Color,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }), ,
                FontFamily,
            ],
            content: '',
            onUpdate: updateButtonStates,
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
            swatchesOnly: true
        });
        editor.commands.setContent(document.querySelector('#content_page_editor_content').value);
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
        isTextAlignLeft.set(editor.isActive('textAlign', {left: true}));
        isTextAlignCenter.set(editor.isActive('textAlign', {center: true}));
        isTextAlignRight.set(editor.isActive('textAlign', {right: true}));
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
        const url = prompt("URL de l'image");
        if (url) {
            editor.chain().focus().setImage({src: url}).run();
        }
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
    <div class="toolbar">
        <ToolbarButton active={$isBold} onClick={toggleBold} icon={'ri-bold'} label="B"/>
        <ToolbarButton active={$isItalic} onClick={toggleItalic} icon={'ri-italic'} label="I"/>
        <ToolbarButton active={$isStrike} onClick={toggleStrike} icon={'ri-strikethrough'} label="S"/>
        <div class="dropdown is-hoverable">
            <div class="dropdown-trigger">
                <button class="button is-white is-small" aria-haspopup="true" aria-controls="dropdown-menu">
                    <span>{$headingTexts[$currentStyle]}</span>
                    <span class="icon is-small">
                    <i class="ri-arrow-down-s-line"></i>
                </span>
                </button>
            </div>
            <div class="dropdown-menu" id="dropdown-menu" role="menu">
                <div class="dropdown-content">
                    <a href="#"
                       class="dropdown-item { $isHeading1 ? 'is-active' : '' }"
                       on:click|preventDefault={() => insertHeading(1)}>
                        <h1>Titre 1</h1>
                    </a>
                    <a href="#"
                       class="dropdown-item { $isHeading2 ? 'is-active' : '' }"
                       on:click|preventDefault={() => insertHeading(2)}>
                        <h2>Titre 2</h2>
                    </a>
                    <a href="#"
                       class="dropdown-item { $isHeading3 ? 'is-active' : '' }"
                       on:click|preventDefault={() => insertHeading(3)}>
                        <h3>Titre 3</h3>
                    </a>
                    <a href="#"
                       class="dropdown-item { $isParagraph ? 'is-active' : '' }"
                       on:click|preventDefault={setParagraph}>
                        <p>Paragraphe</p>
                    </a>
                </div>
            </div>
        </div>
        <ToolbarButton active={$isBulletList} onClick={insertBulletList} icon={'ri-list-unordered'} label="UL"/>
        <ToolbarButton active={$isOrderedList} onClick={insertOrderedList} icon={'ri-list-ordered'} label="OL"/>
        <ToolbarButton onClick={insertImage} icon={'ri-image-add-line'} label="IMG"/>
        <ToolbarButton active={$isBlockquote} onClick={insertBlockquote} icon={'ri-quote-text'} label="BLOCKQUOTE"/>
        <ToolbarButton active={$isTextAlignLeft} onClick={toggleTextAlignLeft} icon={"ri-align-left"}/>
        <ToolbarButton active={$isTextAlignCenter} onClick={toggleTextAlignCenter} icon={"ri-align-center"}/>
        <ToolbarButton active={$isTextAlignRight} onClick={toggleTextAlignRight} icon={"ri-align-right"}/>
        <input type="text" class="coloris" id="color-picker-input" value={$currentColor} on:change={setColor}/>
    </div>
    <div bind:this={editorContainer} class="editor"></div>
</div>