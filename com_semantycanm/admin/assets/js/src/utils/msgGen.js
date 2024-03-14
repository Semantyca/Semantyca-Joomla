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
    const wrapperTemplateString = templateStore.doc.wrapper
        .replace(/&lt;%/g, '<%')
        .replace(/%&gt;/g, '%>');
    //const wrapperTemplateString =  templateStore.doc.wrapper;
    return ejs.render(wrapperTemplateString, data);
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


    const template = '<p>Hello, <%= currentDateFormatted %>!</p>';
    //   const data1 = { name: 'World' };
    //  const output = ejs.render(template, data1);
    //   console.log('test', output); //
    const templateString = decodeEJSTemplate(templateStore.doc.html);

    // const templateString =  templateStore.doc.html;
    console.log('template', templateString);

    let r = ejs.render(templateString, data);
    console.log(r);
    return r;
};

function decodeEJSTemplate(templateString) {
    // Step 1: Decode EJS tags
    let decodedString = templateString
        .replace(/&lt;%/g, '<%')
        .replace(/%&gt;/g, '%>');

    // Step 2: Decode common logical operators within EJS tags
    // This includes <, >, and & within loops and conditionals
    // Note: This regex is simplified for demonstration and might need adjustments
    decodedString = decodedString.replace(/(<%[^%>]*?)&amp;([^%>]*?%>)/g, '$1&$2')
        .replace(/(<%[^%>]*?)&lt;([^%>]*?%>)/g, '$1<$2')
        .replace(/(<%[^%>]*?)&gt;([^%>]*?%>)/g, '$1>$2');

    return decodedString;
}

