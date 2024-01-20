document.addEventListener('DOMContentLoaded', function () {
    const {createApp, ref, onMounted, reactive, watch} = Vue;

    createApp({
        setup() {
            const templateRef = ref(null);
            let templateEditor = null;

            const state = reactive({
                name: '',
                maxArticles: '',
                maxArticlesShort: '',
                html: '',
                main: '',
                dynamic: '',
                ending: '',
                dynamicShort: '',
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

                    }
                });
            };

            const loadContent = async (name) => {
                try {
                    const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok for name ${name}`);
                    }
                    const {data} = await response.json();
                    state['name'] = data.name;
                    state['maxArticles'] = data.maxArticles;
                    state['maxArticlesShort'] = data.maxArticlesShort;
                    state['html'] = data.html;
                    state['main'] = data.main;
                    state['dynamic'] = data.dynamic;
                    state['ending'] = data.ending;
                    state['dynamicShort'] = data.dynamicShort;
                    state['wrapper'] = data.wrapper;
                } catch (error) {
                    console.error(`Problem fetching content for type ${name}:`, error);
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
            });

            return {
                state,
                templateRef,
                initializeTemplateEditor
            };
        }
    }).mount('#vueSection');
});
