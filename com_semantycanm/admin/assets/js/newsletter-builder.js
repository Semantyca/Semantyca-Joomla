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
    let cont = template(data);
    cont += getEnding();
    return cont;
}

function getRandomWebSafeColor() {
    const safeValues = [0, 51, 102, 153, 204, 255]; // Web-safe values for each color channel
    const red = safeValues[Math.floor(Math.random() * safeValues.length)];
    const green = safeValues[Math.floor(Math.random() * safeValues.length)];
    const blue = safeValues[Math.floor(Math.random() * safeValues.length)];

    // Convert each color component to a two-digit hexadecimal value and concatenate
    return `#${red.toString(16).padStart(2, '0')}${green.toString(16).padStart(2, '0')}${blue.toString(16).padStart(2, '0')}`;
}


function generateContent(currentDateFormatted, currentYear) {
    $('#selectedArticles .list-group-item').map(function () {
        return $(this).html();
    }).get();

    let articlesContent = '';
    let selectedArticlesLi = $('#selectedArticles li')
    const totalArticles = selectedArticlesLi.length;
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