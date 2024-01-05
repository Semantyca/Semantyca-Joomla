<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="header-container d-flex justify-content-between align-items-center">
                    <h3><?php echo JText::_('STATISTICS'); ?></h3>
                    <div id="statSpinner" class="spinner">
                        <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                             alt="Loading" class="spinner-icon">
                    </div>
                <div>
                    <input type="hidden" id="totalStats" value="0"/>
                    <input type="hidden" id="currentStats" value="1"/>
                </div>
	            <?php include(__DIR__ . '/../pagination.php'); ?>
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
        const totalPages = getTotalPages1();
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

    function getTotalPages1() {
        const totalRecords = parseInt(document.getElementById('totalStats').value);
        const itemsPerPage = 10;
        return Math.ceil(totalRecords / itemsPerPage);
    }

    function getCurrentPage1() {
        return parseInt(document.getElementById('currentStats').value);
    }

    function goToFirstPage1() {
        refreshStats(1);
    }

    function goToPreviousPage1() {
        const currentPage = getCurrentPage();
        if (currentPage > 1) {
            refreshStats(currentPage - 1);
        }
    }

    function goToNextPage1() {
        const currentPage = getCurrentPage1();
        const totalPages = getTotalPages1();
        if (currentPage < totalPages) {
            refreshStats(currentPage + 1);
        }
    }

    function goToLastPage1() {
        const totalPages = getTotalPages1();
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
