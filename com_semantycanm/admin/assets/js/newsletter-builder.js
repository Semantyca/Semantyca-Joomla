let editedContentStore = {};

function buildContent(currentDateFormatted, currentYear) {
    let selectedArticlesLi = $('#selectedArticles li')
    let articles = [];

    selectedArticlesLi.each(function (index, article) {
        const articleId = article.id;
        const title = article.title;
        const url = normalizeUrl(article.dataset.url);
        let htmlContent = decodeURIComponent(article.dataset.intro);
        let intro = makeImageUrlsAbsolute(htmlContent);
        const category = article.dataset.category;
        let articleObj = {
            id: articleId,
            title: title,
            url: url,
            intro: intro,
            category: category,
            backgroundColor: getRandomWebSafeColor()
        };
        articles.push(articleObj);
    });

    Handlebars.registerHelper('lt', function (value1, value2) {
        return value1 < value2;
    });

    let template = Handlebars.compile(window.myVueState.html);
    let data = {
        bannerUrl: "/joomla/images/2020/EMSA_logo_full_600-ed.png",
        currentDateFormatted: currentDateFormatted,
        currentYear: currentYear,
        articles: articles
    };
    return template(data);
}

function getRandomWebSafeColor() {
    const safeValues = [0, 51, 102, 153, 204, 255];
    const red = safeValues[Math.floor(Math.random() * safeValues.length)];
    const green = safeValues[Math.floor(Math.random() * safeValues.length)];
    const blue = safeValues[Math.floor(Math.random() * safeValues.length)];
    return `#${red.toString(16).padStart(2, '0')}${green.toString(16).padStart(2, '0')}${blue.toString(16).padStart(2, '0')}`;
}

function getWrappedContent(content) {
    let template = Handlebars.compile(window.myVueState.wrapper);
    let data = {
        content: content
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