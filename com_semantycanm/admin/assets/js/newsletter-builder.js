let editedContentStore = {};

function updateNewsletterContent() {
    const currentYear = new Date().getFullYear()
    const currentMonth = new Date().toLocaleString('default', {month: 'long'}).toUpperCase()
    const currentDateFormatted = currentMonth + ' ' + currentYear
    const content = generateContent(currentDateFormatted, currentYear);

    outputHtml.val(content);
    outputHtml.trumbowyg('html', content);
    outputHtml.each(function () {
        const $this = $(this);
        if (!$this.data('trumbowyg')) {
            $this.trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['strong', 'em', 'link'],
                    ['formatting'],
                    ['unorderedList', 'orderedList'],
                    ['removeformat'],
                    ['fullscreen']
                ],
                removeformatPasted: false
            })
                .on('tbwblur', function () {
                    const editedContent = $this.trumbowyg('html');
                    const articleId = $this.data('id');
                    editedContentStore[articleId] = editedContent;
                    $this.html(editedContent);
                    $this.data('intro', encodeURIComponent(editedContent));
                });
        }
    });
}

function updNewsletterContent() {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().toLocaleString('default', {month: 'long'}).toUpperCase();
    const currentDateFormatted = currentMonth + ' ' + currentYear;
    const content = generateContent(currentDateFormatted, currentYear);

    let composerEditor = tinyMCE.get('outputHtml');

    if (composerEditor) {
        composerEditor.setContent(content);
    } else {
        tinyMCE.init({
            selector: '#outputHtml',
            plugins: 'code',
            toolbar: 'code paste removeformat bold italic underline indent outdent',
            menubar: '',
            statusbar: false,
            setup: function (editor) {
                composerEditor = editor;
                editor.on('blur', function (e) {
                    const editedContent = editor.getContent();
                    const articleId = editor.getElement().dataset.id;
                    editedContentStore[articleId] = editedContent;
                });
            }
        });
    }
}


function generateContent(currentDateFormatted, currentYear) {
    $('#selectedArticles .list-group-item').map(function () {
        return $(this).html();
    }).get();

    let articlesContent = '';
    let selectedArticlesLi = $('#selectedArticles li')
    const totalArticles = selectedArticlesLi.length;
    debugger;
    selectedArticlesLi.each(function (index, article) {
        const articleId = article.id;
        const title = article.title;
        const url = normalizeUrl(article.dataset.url);
        let articleContent = editedContentStore[articleId];
        let intro;
        if (articleContent) {
            intro = articleContent;
        } else {
            let htmlContent = decodeURIComponent(article.dataset.intro);
            intro = makeImageUrlsAbsolute(htmlContent);
        }
        const category = article.dataset.category;
        articlesContent += getDynamicEntry(index, title, url, intro, category);
    });

    let template = Handlebars.compile(window.myVueState.main);
    let data = {
        bannerUrl: "/joomla/images/2020/EMSA_logo_full_600-ed.png",
        currentDateFormatted: currentDateFormatted,
        currentYear: currentYear,
        articlesContent: articlesContent
    };
    let cont = template(data);
    cont = cont += getEnding();
    return cont;
}

function getDynamicEntry(index, title, url, intro, category) {
    const parser = new DOMParser();
    const introDoc = parser.parseFromString(intro, "text/html");
    console.log('dynamic: ', window.myVueState.dynamic);
    let template = Handlebars.compile(window.myVueState.dynamic);
    let data = {
        spacerWidth: '100%',
        spacerCellSpacing: '0',
        spacerCellPadding: '0',
        spacerBorder: '0',
        spacerHeight: '10',
        titleFontSize: '25px',
        titleFontFamily: 'Arial, Helvetica, sans-serif',
        title: title,
        url: url,
        intro: introDoc.body.innerHTML,
        category: category
    };
    return template(data);
}

function getEnding() {
    let template = Handlebars.compile(window.myVueState.ending);
    let data = {
        spacerWidth: '100%',
        spacerCellSpacing: '0',
        spacerCellPadding: '0',
        spacerBorder: '0',
        spacerHeight: '10'
    };
    return template(data);
}


function makeImageUrlsAbsolute(articleHtml) {
    let parser = new DOMParser();
    let htmlDoc = parser.parseFromString(articleHtml, 'text/html');
    let images = htmlDoc.getElementsByTagName('img');

    for (let img of images) {
        let currentSrc = img.src;
        img.src = normalizeUrl(currentSrc);
        img.removeAttribute('loading');
        img.removeAttribute('data-path');
    }

    return htmlDoc.body.innerHTML;
}

function normalizeUrl(url) {
    if (url.includes('/administrator/')) {
        return url.replace('/administrator', '');
    }
    return url;
}