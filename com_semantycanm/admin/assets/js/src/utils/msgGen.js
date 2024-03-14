import ejs from 'ejs';
import {useTemplateStore} from "../stores/templateStore";

const makeImageUrlsAbsolute = (htmlContent) => {
    let parser = new DOMParser();
    let doc = parser.parseFromString(htmlContent, 'text/html');
    doc.querySelectorAll('img').forEach(img => {
        img.src = normalizeUrl(img.src);
        img.removeAttribute('loading');
        img.removeAttribute('data-path');
    });
    return doc.body.innerHTML;
};

const normalizeUrl = (url) => {
    return url.includes('/administrator/') ? url.replace('/administrator', '') : url;
};

export const getWrappedContent = (content) => {
    const templateStore = useTemplateStore();
    const data = {content};
    return ejs.render(templateStore.doc.wrapper, data);
};

export const buildContent = (currentDateFormatted, currentYear, selectedArticles) => {
    const templateStore = useTemplateStore();
    const articles = selectedArticles.map(article => ({
        id: article.id,
        title: article.title,
        url: normalizeUrl(article.url),
        intro: makeImageUrlsAbsolute(decodeURIComponent(article.intro)),
        category: article.category,
    }));

    const data = {
        bannerUrl: templateStore.doc.banner,
        currentDateFormatted,
        currentYear,
        articles,
        maxArticles: templateStore.doc.maxArticles,
        maxArticlesShort: templateStore.doc.maxArticlesShort,
    };
    return ejs.render(templateStore.doc.html, data);
};
