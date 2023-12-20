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
                    console.log(articleId);
                    editedContentStore[articleId] = editedContent;
                    $this.html(editedContent);
                    $this.data('intro', encodeURIComponent(editedContent));
                });
        }
    });
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
        const url = normalizeUrl(joomlaHost + article.dataset.url);
        let articleContent = editedContentStore[articleId];
        let intro;
        if (articleContent) {
            intro = articleContent;
        } else {
            let htmlContent = decodeURIComponent(article.dataset.intro);
            intro = makeImageUrlsAbsolute(htmlContent);
        }
        const category = article.dataset.category;
        articlesContent += getStyledEntries(index, title, url, intro, category);

        if (index === 4 && index < totalArticles - 1) {
            articlesContent += window.templates.placeholder();
        }
    });


    return `
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
      <td width="10">
      &nbsp;
      <td>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <!-- Image row -->
      <tr>
      <td>
        <img src=${bannerUrl} alt="banner">
      </td>
      </tr>
      <tr>
      <td style="font-size:18px; color:#003399; padding-top:10px; padding-bottom:10px;">
      ${currentDateFormatted}
      </td>
      </tr>
      <tr>
      <td>
      ${articlesContent}
      </td>
      </tr>
      <!-- Footer content row -->
      <tr>
      <td>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr><td style="padding:5px;">&nbsp;</td></tr>
      <tr><td style="font-size: 14px; border-top: 1px solid #003399;">&copy; ${currentYear}, European Maritime Safety Agency</td></tr>
      </table>
      </td>
      </tr>
      </table>
      </td>
      <td width="10">
      &nbsp;
      </td>
      </tr>
      </table>
      `;
}

function getStyledEntries(index, title, url, intro, category) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(intro, "text/html");
    const imgTags = doc.getElementsByTagName("img");
    for (let i = 0; i < imgTags.length; i++) {
        imgTags[i].removeAttribute("height");
        imgTags[i].setAttribute("width", "100%");
        imgTags[i].setAttribute("style", "margin-bottom: 2%;");
    }
    const pTags = doc.getElementsByTagName("p");
    for (let i = 0; i < pTags.length; i++) {
        pTags[i].setAttribute("style", "font-size: 18px;");
    }

    intro = doc.body.innerHTML;
    if (templates[index]) {
        return templates[index](title, url, intro, category);
    } else {
        return templates.default(title, url, intro, category);
    }
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