import Handlebars from 'handlebars';
import {useTemplateStore} from "../stores/templateStore";

const colorList = ['#152E52', '#AEC127', '#5CA550', '#DF5F5A', '#F9AE4F'];
let colorIndex = 0;
export const getWrappedContent = (content) => {
    const templateStore = useTemplateStore();
    let template = Handlebars.compile(templateStore.doc.wrapper);
    let data = {
        content: content
    };
    return template(data);
}
export const buildContent = (currentDateFormatted, currentYear, selectedArticles) => {
    resetColorIndex();
    const templateStore = useTemplateStore();
    let articles = [];
    selectedArticles.forEach((article) => {
        const articleId = article.id;
        const title = article.title;
        const url = normalizeUrl(article.url);
        let htmlContent = decodeURIComponent(article.intro);
        let intro = makeImageUrlsAbsolute(htmlContent);
        const category = article.category;

        let articleObj = {
            id: articleId,
            title: title,
            url: url,
            intro: intro,
            category: category,
            backgroundColor: getSequentialColor()
        };
        articles.push(articleObj);
    });

    Handlebars.registerHelper('lt', function (value1, value2) {
        return value1 < value2;
    });

    let template = Handlebars.compile(templateStore.doc.html);
    let data = {
        bannerUrl: templateStore.doc.banner,
        currentDateFormatted: currentDateFormatted,
        currentYear: currentYear,
        articles: articles,
        maxArticles: templateStore.doc.maxArticles,
        maxArticlesShort: templateStore.doc.maxArticlesShort
    };
    return template(data);
};

export const resetColorIndex = () => {
    colorIndex = 0;
}

const getSequentialColor = () => {
    const color = colorList[colorIndex];
    colorIndex = (colorIndex + 1) % colorList.length;
    return color;
}

const getRandomWebSafeColor = () => {
    const safeValues = [0, 51, 102, 153, 204, 255];
    const red = safeValues[Math.floor(Math.random() * safeValues.length)];
    const green = safeValues[Math.floor(Math.random() * safeValues.length)];
    const blue = safeValues[Math.floor(Math.random() * safeValues.length)];
    return `#${red.toString(16).padStart(2, '0')}${green.toString(16).padStart(2, '0')}${blue.toString(16).padStart(2, '0')}`;
}

const makeImageUrlsAbsolute = (articleHtml) => {
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


const normalizeUrl = (url) => {
    if (url.includes('/administrator/')) {
        return url.replace('/administrator', '');
    }
    return url;
}

