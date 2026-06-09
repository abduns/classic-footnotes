/* Footnote and Reference — Classic Editor helpers */
(function (window) {
    'use strict';

    var labels = window.csfnEditorL10n || {};

    function label(key, fallback) {
        return labels[key] || fallback;
    }

    function clean(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function attr(value) {
        return clean(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/\[/g, '&#91;')
            .replace(/\]/g, '&#93;');
    }

    function buildShortcode(data) {
        var attrs = [];
        var note = clean(data && data.note);
        var url = clean(data && data.url);
        var title = clean(data && data.title);

        if (url) {
            attrs.push('url="' + attr(url) + '"');
        }

        if (title) {
            attrs.push('title="' + attr(title) + '"');
        }

        if (note) {
            attrs.push('text="' + attr(note) + '"');
        }

        if (!attrs.length) {
            return '';
        }

        return '[fn ' + attrs.join(' ') + ']';
    }

    function addTinyMcePlugin() {
        if (!window.tinymce || !window.tinymce.PluginManager) {
            return;
        }

        window.tinymce.PluginManager.add('csfn_reference_note', function (editor) {
            editor.addButton('csfn_reference_note', {
                title: label('buttonTitle', 'Reference note'),
                icon: 'csfn-reference-note',
                onclick: function () {
                    editor.windowManager.open({
                        title: label('dialogTitle', 'Add reference note'),
                        body: [
                            {
                                type: 'textbox',
                                name: 'note',
                                label: label('noteLabel', 'Footnote text'),
                                multiline: true,
                                minWidth: 350,
                                minHeight: 90
                            },
                            {
                                type: 'textbox',
                                name: 'url',
                                label: label('urlLabel', 'Source URL')
                            },
                            {
                                type: 'textbox',
                                name: 'title',
                                label: label('titleLabel', 'Source title')
                            }
                        ],
                        buttons: [
                            {
                                text: label('insertLabel', 'Insert'),
                                subtype: 'primary',
                                classes: 'csfn-insert',
                                onclick: 'submit'
                            },
                            {
                                text: label('cancelLabel', 'Cancel'),
                                onclick: 'close'
                            }
                        ],
                        onsubmit: function (event) {
                            var shortcode = buildShortcode(event.data || {});

                            if (shortcode) {
                                editor.insertContent(shortcode);
                            }
                        }
                    });
                }
            });
        });
    }

    function addQuicktagsButton() {
        if (window.csfnEditorQuicktagAdded || !window.QTags) {
            return;
        }

        window.csfnEditorQuicktagAdded = true;

        window.QTags.addButton(
            'csfn_reference_note',
            'fn',
            function () {
                var note = window.prompt(label('notePrompt', 'Footnote text:'), '');
                var shortcode;

                if (null === note) {
                    return;
                }

                shortcode = buildShortcode({ note: note });

                if (shortcode) {
                    window.QTags.insertContent(shortcode);
                }
            },
            '',
            '',
            label('buttonTitle', 'Reference note')
        );
    }

    addTinyMcePlugin();
    addQuicktagsButton();
})(window);
