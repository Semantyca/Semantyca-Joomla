<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="header-container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center justify-content-start" style="flex-grow: 1;">
                    <h3><?php echo JText::_('STATISTICS'); ?></h3>
                    <div id="statSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status"
                         style="display: none; margin-left: 10px;">
                        <span class="visually-hidden"><?php echo JText::_('LOADING'); ?></span>
                    </div>
                </div>
                <div>
                    <input type="hidden" id="total" value="0"/>
                    <input type="hidden" id="current" value="1"/>
                </div>
                <div class="pagination-container mb-2 me-3">
                    <a class="btn btn-primary me-2" href="#" id="goToFirstPage"><?php echo JText::_('FIRST'); ?></a>
                    <a class="btn btn-primary me-2" href="#"
                       id="goToPreviousPage"><?php echo JText::_('PREVIOUS'); ?></a>
                    <a class="btn btn-primary me-2" href="#" id="goToNextPage"><?php echo JText::_('NEXT'); ?></a>
                    <a class="btn btn-primary" href="#" id="goToLastPage"><?php echo JText::_('LAST'); ?></a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 5%;">
                            <button class="btn btn-outline-secondary refresh-button" type="button"
                                    id="refreshStatsButton">
                                <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/refresh.png"
                                     alt="Refresh" class="refresh-icon">
                            </button>
                        </th>
                        <th style="width: 20%;"><?php echo JText::_('RECIPIENTS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('STATUS'); ?></th>
                        <th style="width: 15%;"><?php echo JText::_('SEND_TIME'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('OPENS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('CLICKS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('UNSUBS'); ?></th>
                        <th style="width: 20%;"><?php echo JText::_('NEWSLETTER'); ?></th>
                    </tr>
                    </thead>
                    <tbody id="statsList">
                </table>
            </div>
        </div>
    </div>
</div>


<script>
    const ITEMS_PER_PAGE = 10;

    $(document).ready(function () {
        document.getElementById('nav-stats-tab').addEventListener('shown.bs.tab', () => refreshStats(1));
        document.getElementById('refreshStatsButton').addEventListener('click', () => refreshStats(getCurrentPage()));
        document.getElementById('goToFirstPage').addEventListener('click', () => goToFirstPage());
        document.getElementById('goToPreviousPage').addEventListener('click', () => goToPreviousPage());
        document.getElementById('goToNextPage').addEventListener('click', () => goToNextPage());
        document.getElementById('goToLastPage').addEventListener('click', () => goToLastPage());
    });

    function refreshStats(currentPage) {
        showSpinner('statSpinner');

        currentPage = Math.max(currentPage, 1);
        const totalPages = getTotalPages();
        currentPage = Math.min(currentPage, totalPages);

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=Stat.findAll&page=' + currentPage + '&limit=' + ITEMS_PER_PAGE,
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    $('#statsList').html(composeStatsContent(response.data.documents));
                    $('#total').val(response.data.total);
                    $('#current').val(currentPage);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus, errorThrown);
            },
            complete: function () {
                hideSpinner('statSpinner');
            }
        });
    }

    function getTotalPages() {
        const totalRecords = parseInt(document.getElementById('total').value);
        const itemsPerPage = 10;
        return Math.ceil(totalRecords / itemsPerPage);
    }

    function getCurrentPage() {
        return parseInt(document.getElementById('current').value);
    }

    function goToFirstPage() {
        refreshStats(1);
    }

    function goToPreviousPage() {
        const currentPage = getCurrentPage();
        if (currentPage > 1) {
            refreshStats(currentPage - 1);
        }
    }

    function goToNextPage() {
        const currentPage = getCurrentPage();
        const totalPages = getTotalPages();
        if (currentPage < totalPages) {
            refreshStats(currentPage + 1);
        }
    }

    function goToLastPage() {
        const totalPages = getTotalPages();
        refreshStats(totalPages);
    }

    function composeStatsContent(data) {
        let html = '';
        data.forEach(function (stat) {
            html += '<tr data-groupid="' + stat.id + '">';
            html += '<td style="width: 5%;"><input type="checkbox" name="selectedItems[]" value="' + stat.id + '"></td>';
            html += '<td style="width: 20%;">' + stat.recipients + '</td>';
            html += '<td style="width: 10%;">' + getBadge(stat.status) + '</td>';
            html += '<td style="width: 15%;">' + (stat.sent_time ? stat.sent_time : 'N/A') + '</td>';
            html += '<td style="width: 10%;">' + stat.opens + '</td>';
            html += '<td style="width: 10%;">' + stat.clicks + '</td>';
            html += '<td style="width: 10%;">' + stat.unsubs + '</td>';
            html += '<td style="width: 20%;">' + stat.newsletter_id + '</td>';
            html += '</tr>';
        });
        return html;
    }


    function getBadge(status) {
        switch (status) {
            case -1:
                return '<span class="badge bg-danger">error</span>'
            case 1:
                return '<span class="badge bg-warning text-dark">sending</span>'
            case 2:
                return '<span class="badge bg-info text-light">sent</span>'
            case 3:
                return '<span class="badge bg-success">read</span>'
            default:
                return '<span class="badge bg-secondary">unknown</span>'
        }
    }
</script>
