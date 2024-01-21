document.addEventListener('DOMContentLoaded', function () {
    const TEMPLATE_SPINNER = 'templateSpinner';
    const {createApp, ref, onMounted, reactive, watch} = Vue;

    createApp({
        setup() {
            const templateRef = ref(null);
            let templateEditor = null;

            const state = reactive({
                id: 0,
                name: '',
                maxArticles: '',
                maxArticlesShort: '',
                html: '',
                wrapper: ''
            });

            window.myVueState = state;

            const initializeTemplateEditor = async () => {
                templateEditor = tinymce.init({
                    target: templateRef.value,
                    plugins: 'code',
                    toolbar: 'code',
                    menubar: '',
                    statusbar: false,
                    height: 500,
                    setup: function (editor) {
                        editor.on('init', function () {
                            editor.setContent(state['html']);
                        });
                        editor.on('change', function () {
                            state['html'] = editor.getContent();
                        });
                        editor.ui.registry.addButton('myCustomToolbarButton', {
                            text: 'My Custom Button',
                            onAction: function () {
                                alert('Button clicked!');
                            }
                        });
                    }
                });
            };

            const loadContent = async (name) => {
                startLoading(TEMPLATE_SPINNER);
                try {
                    const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok for name ${name}`);
                    }
                    const {data} = await response.json();
                    state['id'] = data.id;
                    state['name'] = data.name;
                    state['maxArticles'] = data.maxArticles;
                    state['maxArticlesShort'] = data.maxArticlesShort;
                    state['html'] = data.content;
                    state['wrapper'] = data.wrapper;
                    stopLoading(TEMPLATE_SPINNER);
                } catch (error) {
                    console.error(`Problem fetching content for type ${name}:`, error);
                    stopLoading(TEMPLATE_SPINNER);
                }
            };

            const saveTemplate = async () => {
                startLoading(TEMPLATE_SPINNER);
                const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(state['id'])}`;
                const data = {
                    html: state['html']
                };

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error, status = ${response.status}`);
                    }

                    console.log('Template saved successfully');
                    stopLoading(TEMPLATE_SPINNER);
                } catch (error) {
                    showErrorBar(`${endpoint}`, error.message);
                    stopLoading(TEMPLATE_SPINNER);
                }
            };


            const addSaveButtonListener = () => {
                const saveButton = document.getElementById('saveTemplate');
                if (saveButton) {
                    saveButton.addEventListener('click', saveTemplate);
                }
            };

            watch(() => state['html'], (newContent) => {
                if (templateEditor && templateEditor.activeEditor) {
                    templateEditor.activeEditor.setContent(newContent || '');
                }
            });


            onMounted(async () => {
                await loadContent('classic');
                initializeTemplateEditor();
                addSaveButtonListener();
            });

            return {
                state,
                templateRef,
                initializeTemplateEditor
            };
        }
    }).mount('#vueSection');
});
