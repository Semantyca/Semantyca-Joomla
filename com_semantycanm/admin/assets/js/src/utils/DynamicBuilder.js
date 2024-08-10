import ejs from "ejs";

export default class DynamicBuilder {

    constructor(template) {
        this.template = template;
        this.variables = {};
    }

    normalizeUrl(url) {
        return url.includes('/administrator/') ? url.replace('/administrator', '') : url;
    }

    makeImageUrlsAbsolute(htmlContent) {
        let parser = new DOMParser();
        let doc = parser.parseFromString(htmlContent, 'text/html');
        doc.querySelectorAll('img').forEach(img => {
            img.src = this.normalizeUrl(img.src);
            img.removeAttribute('loading');
            img.removeAttribute('data-path');
        });
        return doc.body.innerHTML;
    }

    addVariable(name, value) {
        this.variables[name] = value;
    }

    buildContent() {
        if (Array.isArray(this.variables['articles'])) {
            this.variables['articles'] = this.variables['articles'].map(article => ({
                ...article,
                url: this.normalizeUrl(article.url),
                intro: this.makeImageUrlsAbsolute(decodeURIComponent(article.intro)),
            }));
        } else {
            this.variables['articles'] = [];
        }

        return ejs.render(this.template.content, this.variables);
    }

    getWrappedContent = () => {
        let msgBody =  this.buildContent();

        if (this.template.wrapper.trim() !== '') {
            return ejs.render(this.template.wrapper, msgBody);
        } else {
            return msgBody;
        }
    };

}