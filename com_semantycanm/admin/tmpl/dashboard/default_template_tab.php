<div class="container mt-5" id="vueSection">
    <div id="templateSpinner" class="loading-spinner"></div>
    <div class="col-md-12 d-flex flex-column">
        <div ref="templateRef" style="border: 1px solid gray">
        </div>
    </div>
    <div class="col-mt-3" style="margin-top: 10px;"> <!-- Added margin-top -->
        <button id="saveTemplate" class="btn btn-success"
                style="margin-right: 10px;"><?php echo JText::_('SAVE'); ?></button> <!-- Added margin-right -->

    </div>
    <div ref="codeEditorRef" id="codeEditor"></div>


</div>
