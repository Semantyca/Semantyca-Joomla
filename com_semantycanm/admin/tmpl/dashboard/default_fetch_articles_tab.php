<?php
defined('_JEXEC') or die;

//require_once __DIR__ . '/template_source_helper.php';

$imageUrl = JUri::root() . 'media/com_semantycanm/images/staff-newsletter-banner.jpg';

$app = Joomla\CMS\Factory::getApplication();
$doc = $app->getDocument();


?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Available Articles</h3>
            <label for="article-search"></label>
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
                <button id="preview-button" class="btn mb-2">Preview</button>
                <button id="reset-button" class="btn" style="background-color: #152E52; color: white;">Reset</button>
                <button id="copy-code-button" class="btn btn-info mb-2">Copy Code</button>
            </div>
            <label for="output-html"></label><textarea id="output-html" class="form-control mt-3" rows="10"></textarea>
        </div>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Newsletter Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="preview-content"></div>
            </div>
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

    $('#preview-button').click(function () {
        $('#preview-content').html($('#output-html').val());
        $('#previewModal').modal('show');
    });

    $('#article-search').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        const filteredArticles = articlesData.filter(article => article.title.toLowerCase().includes(searchTerm));
        renderArticles(filteredArticles);
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
            newLiEntry.textContent = draggedElement.textContent;
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
        // Process img tags: Remove height and set width to 100%
        var parser = new DOMParser();
        var doc = parser.parseFromString(intro, "text/html");

        // Modify img tags
        var imgTags = doc.getElementsByTagName("img");
        for (var i = 0; i < imgTags.length; i++) {
            imgTags[i].removeAttribute("height");
            imgTags[i].setAttribute("width", "100%");
            imgTags[i].setAttribute("style", "margin-bottom: 2%;");
        }

        // Modify p tags with inline CSS styling
        var pTags = doc.getElementsByTagName("p");
        for (var i = 0; i < pTags.length; i++) {
            pTags[i].setAttribute("style", "font-size: 18px;");
        }

        intro = doc.body.innerHTML;

        // Check if a specific template exists for the given index
        if (templates[index]) {
            return templates[index](title, url, intro, category);
        } else {
            // Return the default template if no specific template exists for the index
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
