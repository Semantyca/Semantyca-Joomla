<?php
defined('_JEXEC') or die;

// Add the Typeahead.js library.
$doc = JFactory::getDocument();
$doc->addScript('/joomla/media/com_semantycanm/js/typeahead.bundle.js');

// Add the Typeahead.js initialization code.
$doc->addScriptDeclaration(
	"jQuery(document).ready(function($) {
       $('#article-search').typeahead({
           source: function(query, process) {
               return $.get('/articles/search', { searchTerms: query }, function(data) {
                  return process(data);
               });
           }
       });
   });"
);
?>
<head>
    <title></title>
    <style>
        #selected-articles {
            min-height: 450px;
            background-color: #f8f9fa;
            border: 2px dashed #adb5bd;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        #selected-articles:empty {
            border: 3px dashed #adb5bd;
        }

        #preview-button {
            background-color: green;
            color: white;
        }
        #articles-list .list-group-item {
            cursor: grab; /* cursor will appear as an open hand when hovering over articles */
        }

        #articles-list .list-group-item:active {
            cursor: grabbing; /* cursor will appear as a closed hand when an article is being dragged */
        }
        .btn-group .btn {
            margin-right: 5px; /* Space between buttons */
            height: 38px; /* Ensures that all buttons have the same height */
        }

        /* Remove the margin for the last button to ensure that there's no extra space on the far right */
        .btn-group .btn:last-child {
            margin-right: 0;
        }

    </style>
</head>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Available Articles</h3>
            <label for="article-search"></label>
            <input type="text" id="article-search" class="form-control mb-2" placeholder="Search articles...">
            <ul id="articles-list" class="list-group">
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
                <!--<button id="generate-newsletter" class="btn btn-primary">Generate Newsletter</button>-->
                <!-- <button id="toggle-editor" class="btn btn-secondary mb-2">Toggle Editor</button> -->
                <button id="preview-button" class="btn mb-2">Preview</button>
                <button id="reset-button" class="btn" style="background-color: #152E52; color: white;">Reset</button>
                <button id="copy-code-button" class="btn btn-info mb-2">Copy Code</button>
            </div>
            <label for="output-html"></label><textarea id="output-html" class="form-control mt-3" rows="10"></textarea>
        </div>
    </div>
</div>

<!-- Preview Modal -->
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

