import './bootstrap';
import CodeMirror from 'codemirror';
import 'codemirror/lib/codemirror.css';
import 'codemirror/mode/css/css';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/xml/xml';

document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.querySelector('[data-template-html-editor]');
    if (!textarea) return;

    const editor = CodeMirror.fromTextArea(textarea, {
        indentUnit: 2,
        lineNumbers: true,
        lineWrapping: true,
        mode: 'htmlmixed',
        tabSize: 2
    });

    editor.getWrapperElement().setAttribute('data-html-code-editor', '');
    editor.on('change', function () {
        editor.save();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    });
});
