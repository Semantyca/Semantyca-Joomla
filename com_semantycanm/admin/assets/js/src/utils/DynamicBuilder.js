import ejs from "ejs";

export default class DynamicContentBuilder {
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
        console.log('DynamicBuilder: message variables', this.variables);
        this.variables['articles'] = this.variables['articles'].map(article => ({
            ...article,
            url: this.normalizeUrl(article.url),
            intro: this.makeImageUrlsAbsolute(decodeURIComponent(article.intro)),
        }));
        return ejs.render(this.template.content, this.variables);
    }

    getWrappedContent = (content) => {
        let msgBody = { content: content };

        if (this.template.wrapper && this.template.wrapper.trim() !== '') {
            return ejs.render(this.template.wrapper, msgBody);
        } else {
            return content;
        }
    };

}