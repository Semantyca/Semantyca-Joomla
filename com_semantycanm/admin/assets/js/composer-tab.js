document.addEventListener('DOMContentLoaded', function () {
    const {createApp, ref, onMounted, nextTick, watch, reactive} = Vue;
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleString('default', {month: 'long'}).toUpperCase();
    const currentDateFormatted = `${currentMonth} ${currentYear}`;

    createApp({
        setup() {
            const articles = ref([]);
            const articlesListRef = ref(null);
            const selectedArticlesListRef = ref(null);
            const composerRef = ref(null);
            let composerEditor = null;

            const state = reactive({
                editorCont: ''
            });

            const resetFunction = async () => {
                await fetchArticles('');
                composerEditor.setContent('');
                state.editorCont = '';
                selectedArticlesListRef.value.innerHTML = '';
            };

            const copyContentToClipboard = () => {
                const completeHTML = getWrappedContent(composerEditor.getContent());
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = completeHTML;
                document.body.appendChild(tempTextArea);
                tempTextArea.select();
                const successful = document.execCommand('copy');
                if (successful) {
                    showAlertBar('HTML code copied to clipboard!', "info");
                } else {
                    showAlertBar('Failed to copy. Please try again.', "warning");
                }
                document.body.removeChild(tempTextArea);
            };

            const next = () => {
                const messageContent = document.getElementById('messageContent');
                messageContent.value = getWrappedContent(composerEditor.getContent());
                $('#nav-newsletters-tab').tab('show');
            };

            const fetchArticles = async (searchTerm) => {
                startLoading();
                try {
                    const url = 'index.php?option=com_semantycanm&task=Article.search&q=' + encodeURIComponent(searchTerm);
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
                    }
                    const data = await response.json();
                    articles.value = data.data.map(a => ({
                        id: a.id,
                        title: a.title,
                        url: a.url,
                        category: a.category,
                        intro: encodeURIComponent(a.introtext)
                    }));
                    stopLoading();
                } catch (error) {
                    console.error(`Problem fetching articles:`, error);
                    stopLoading();
                }
            };

            const applyAndDropSet = (lists) => {
                lists.forEach(list => {
                    Sortable.create(list, {
                        group: {
                            name: 'shared',
                            pull: true,
                            put: true
                        },
                        animation: 150,
                        sort: false,
                        onEnd: (evt) => {
                            updateComposerContent();
                        }
                    });
                });
            };

            const updateComposerContent = () => {
                composerEditor.setContent(buildContent(currentDateFormatted, currentYear));
            };

            onMounted(async () => {
                await fetchArticles('');

                await nextTick();
                composerEditor = tinymce.init({
                    target: composerRef.value,
                    plugins: 'code',
                    toolbar: 'code paste removeformat bold italic underline indent outdent',
                    menubar: '',
                    statusbar: false,
                    height: 300,
                    setup: function (editor) {
                        editor.on('init', function () {
                            editor.setContent(state.editorCont);
                            composerEditor = editor;
                        });
                        editor.on('change', function () {
                            state.editorCont = editor.getContent();
                        });
                    }
                });

                nextTick(() => {
                    applyAndDropSet([articlesListRef.value, selectedArticlesListRef.value]);
                    const resetBtn = document.getElementById('resetBtn');
                    resetBtn?.addEventListener('click', resetFunction);
                    const copyCodeBtn = document.getElementById('copyCodeBtn');
                    copyCodeBtn?.addEventListener('click', copyContentToClipboard);
                    const nextBtn = document.getElementById('nextBtn');
                    nextBtn?.addEventListener('click', next);
                });

            });


            return {
                articles,
                state,
                articlesListRef,
                selectedArticlesListRef,
                composerRef
            };
        }
    }).mount('#composerSection');
});

function startLoading() {
    document.getElementById('loadingSpinner').style.display = 'block';
}

function stopLoading() {
    document.getElementById('loadingSpinner').style.display = 'none';
}


