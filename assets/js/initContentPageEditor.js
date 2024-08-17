// file: assets/initContentPageEditor.js
import { createApp } from 'svelte';
import TiptapEditor from '../svelte/TiptapEditor.svelte';

const app = new TiptapEditor({
    target: document.getElementById('editor')
});

export default app;