document.addEventListener('DOMContentLoaded', function () {
    const {createApp, ref, onMounted, reactive, nextTick, watch} = Vue;

    createApp({
        setup() {
            const templateRef = ref(null);
            let templateEditor = null;

            const state = reactive({
                name: '',
                maxArticles: '',
                maxArticlesShort: '',
                main: '',
                dynamic: '',
                ending: '',
                dynamicShort: ''
            });

            window.myVueState = state;

            const initializeTemplateEditor = async () => {
                await nextTick();
                templateEditor = tinymce.init({
                    target: templateRef.value,
                    plugins: 'code',
                    toolbar: 'code',
                    menubar: '',
                    statusbar: false,
                    height: 300,
                    setup: function (editor) {
                        editor.on('init', function () {
                            editor.setContent(state['main']);

                        });
                        editor.on('change', function () {
                            state['main'] = editor.getContent();
                        });

                    }
                });
            };

            const loadContent = async (name) => {
                try {
                    const url = `/joomla/administrator/index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok for name ${name}`);
                    }
                    const {data} = await response.json();
                    state['name'] = data.name;
                    state['maxArticles'] = data.maxArticles;
                    state['maxArticlesShort'] = data.maxArticlesShort;
                    state['main'] = data.main;
                    state['dynamic'] = data.dynamic;
                    state['ending'] = data.ending;
                    state['dynamicShort'] = data.dynamicShort;
                } catch (error) {
                    console.error(`Problem fetching content for type ${name}:`, error);
                }
            };


            watch(() => state['main'], (newContent) => {
                if (templateEditor && templateEditor.activeEditor) {
                    const contentToSet = newContent ?? '';
                    if (templateEditor.activeEditor.getContent() !== contentToSet) {
                        templateEditor.activeEditor.setContent(contentToSet);
                    }
                }
            });


            onMounted(() => {
                loadContent('classic');
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
