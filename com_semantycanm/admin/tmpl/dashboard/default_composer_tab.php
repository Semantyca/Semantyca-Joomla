<?php

defined('_JEXEC') or die;

//require_once __DIR__ . '/template_source_helper.php';

//$imageUrl = "http://localhost/joomla/media/com_semantycanm/files/staff-newsletter-banner.jpg";
$imageUrl = "https://dev.absolute.lv/emsa/intranet-0923/images/2020/staff-newsletter-banner.jpg";
$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();


?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h3>Available Articles</h3>
            <input type="text" id="article-search" class="form-control mb-2" placeholder="Search articles...">
            <ul id="articles-list" class="list-group" style="height: 350px !important; overflow-y: auto;">
				<?php
				foreach ($this->articlesList as $article): ?>
                    <li class="list-group-item"
                        <?php echo 'id="'.$article->id.'" title="'.$article->title.'"  url="'.$article->url.'" category="'.$article->category.'" intro="'.$article->introtext.'"'; ?>>
                        <strong><?php echo $article->category?></strong><br><?php echo $article->title; ?>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h3>Selected Articles</h3>
            <ul id="selected-articles" class="list-group">

            </ul>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="btn-group">
                <button id="reset-button" class="btn" style="background-color: #152E52; color: white;">Reset</button>
                <button id="copy-code-button" class="btn btn-info mb-2">Copy Code</button>
                <button id="send-to-textarea-btn" class="btn btn-info mb-2">Send to Newsletter</button>
            </div>
            <label for="output-html"></label><textarea id="output-html" class="form-control mt-3" rows="10"></textarea>
        </div>
    </div>
</div>

<script>
    let outputHtml = $('#output-html')

    $('#reset-button').click(function () {
        outputHtml.val('');
        outputHtml.trumbowyg('html', '')
        $('#selected-articles').empty()
    });

    $('#article-search').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length >= 3) {
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=article.search',
                type: 'GET',
                data: {
                    q: searchTerm
                },
                dataType: 'json',
                success: function (response) {
                    console.log(response.data);
                    $('#articles-list').empty();
                    response.data.forEach(article => {
                        $('#articles-list').append(`
                  <li class="list-group-item"
                      id="${article.id}"
                      title="${article.title}"
                      url="${article.url}"
                      category="${article.category}"
                      intro="${article.introtext}">
                      <strong>${article.category}</strong><br>${article.title}
                  </li>
               `);
                    });

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error: ' + textStatus + ' ' + errorThrown);
                }
            });
        }
    });

    $('#copy-code-button').click(function () {
        const completeHTML = getFullTemplate($('#output-html').val());
        const tempTextArea = $('<textarea>').val(completeHTML).appendTo('body').select();
        const successful = document.execCommand('copy');
        if (successful) {
            alert('HTML code copied to clipboard!');
        } else {
            alert('Failed to copy. Please try again.');
        }
        tempTextArea.remove();
    });

    $('#send-to-textarea-btn').click(function () {
        var outputHtml = document.getElementById('output-html').value;
        var messageContent = document.getElementById('messageContent');
        messageContent.value = outputHtml;
        $('#nav-newsletters-tab').tab('show');
    });


    let editedContentStore = {};
    let articlesList = document.getElementById('articles-list');
    let selectedArticles = document.getElementById('selected-articles');
    let sortableArticlesList = Sortable.create(articlesList, {
        group: {
            name: 'shared',
            pull: 'clone',
            nut: false
        },
        animation: 150,
        sort: false
    });

    sortableArticlesList.option("onEnd", function (evt) {
        let draggedElement = evt.item;
        let duplicate = Array.from(selectedArticles.children).some(li => {
            return li.dataset.id === draggedElement.id;
        });
        if (!duplicate) {
            let newLiEntry = document.createElement('li');
            newLiEntry.textContent = draggedElement.attributes.title.nodeValue;
            //TODO it needs to be styled
            newLiEntry.dataset.id = draggedElement.id;
            newLiEntry.dataset.title = draggedElement.title;
            newLiEntry.dataset.url = draggedElement.attributes.url.nodeValue;
            newLiEntry.dataset.category = draggedElement.attributes.category.nodeValue;
            newLiEntry.dataset.intro = draggedElement.attributes.intro.nodeValue;
            newLiEntry.className = "list-group-item";
            newLiEntry.addEventListener("click", function() {
					this.parentNode.removeChild(this);
				});

            selectedArticles.appendChild(newLiEntry);
            updateNewsletterContent();
        }
    });

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
                        editedContentStore[articleId] = editedContent;
                        $this.html(editedContent);
                        $this.data('intro', encodeURIComponent(editedContent));
                    });
            }
        });
    }

    function getStyledContent(index, title, url, intro, category) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(intro, "text/html");
        const imgTags = doc.getElementsByTagName("img");
        for (let i = 0; i < imgTags.length; i++) {
            imgTags[i].removeAttribute("height");
            imgTags[i].setAttribute("width", "100%");
            imgTags[i].setAttribute("style", "margin-bottom: 2%;");
        }
        const pTags = doc.getElementsByTagName("p");
        for (var i = 0; i < pTags.length; i++) {
            pTags[i].setAttribute("style", "font-size: 18px;");
        }

        intro = doc.body.innerHTML;
        if (templates[index]) {
            return templates[index](title, url, intro, category);
        } else {
            return templates.default(title, url, intro, category);
        }
    }

    function generateContent(currentDateFormatted, currentYear) {
        const selectedArticles = $('#selected-articles .list-group-item').map(function () {
            return $(this).html();
        }).get();

        let articlesContent = '';
        let selectedArticlesLi = $('#selected-articles li')
        const totalArticles = selectedArticlesLi.length;

        selectedArticlesLi.each(function (index, article) {
            const articleId = $(article).data('id');
            const title = $(article).data('title');
            const url = $(article).data('url');
            const intro = editedContentStore[articleId] ? editedContentStore[articleId] : decodeURIComponent($(article).data('intro'));
            const category = $(article).data('category');
            articlesContent += getStyledContent(index, title, url, intro, category);

            // Insert the placeholder after the fifth article if there are more articles to come
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
        <?php echo JHtml::_('image', $imageUrl, 'EMSA', ['alt' => 'EMSA Staff newsletter']); ?>
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

</script>
