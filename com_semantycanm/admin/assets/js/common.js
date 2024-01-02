function showSpinner(spinnerId) {
    const spinner = document.getElementById(spinnerId);
    spinner.style.display = 'block';
    spinner.style.opacity = '1';
}

function hideSpinner(spinnerId) {
    const spinner = document.getElementById(spinnerId);
    spinner.style.display = 'none';
    spinner.style.opacity = '0';
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

function showAlertBar(message, type = 'danger', duration = 5000) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
<div class="alert alert-${type} alert-dismissible fade" role="alert">
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>`;
    alertPlaceholder.append(wrapper);
    const alert = wrapper.firstElementChild;
    setTimeout(() => alert.classList.add('show'), 10);
    const closeButton = wrapper.querySelector('.btn-close');
    closeButton.onclick = function () {
        fadeOutAndRemoveAlert(alert);
    };
    setTimeout(function () {
        fadeOutAndRemoveAlert(alert);
    }, duration);
}

function fadeOutAndRemoveAlert(alert) {
    alert.classList.remove('show');
    alert.ontransitionend = () => alert.remove();
}
