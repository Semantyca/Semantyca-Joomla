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

function getTotalPages(hiddenTotalInput) {
    const totalRecords = parseInt(document.getElementById(hiddenTotalInput).value);
    return Math.ceil(totalRecords / ITEMS_PER_PAGE);
}

function getCurrentPage(hiddenCurrentPageInput) {
    return parseInt(document.getElementById(hiddenCurrentPageInput).value);
}

function goToFirstPage(func) {
    func(1);
}

function goToPreviousPage(currentPageNumHolder, func) {
    const currentPage = getCurrentPage(currentPageNumHolder);
    if (currentPage > 1) {
        func(currentPage - 1);
    }
}

function goToNextPage(currentPageNumHolder, totalValueHolder, func) {
    const currentPage = getCurrentPage(currentPageNumHolder);
    const totalPages = getTotalPages(totalValueHolder);
    if (currentPage < totalPages) {
        func(currentPage + 1);
    }
}

function goToLastPage(totalValueHolder, func) {
    const totalPages = getTotalPages(totalValueHolder);
    func(totalPages);
}


function createRowButton(buttonText, buttonClass, eventHandler) {
    const button = document.createElement('button');
    button.className = buttonClass;
    button.textContent = buttonText;
    button.style.height = '30px';
    button.style.width = '65px';
    if (eventHandler) {
        button.addEventListener('click', eventHandler);
    }
    return button;
}

function createRowCheckBox(id) {
    const tdCheckbox = document.createElement('td');
    tdCheckbox.className = 'col-1';
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = 'selectedItems[]';
    checkbox.value = id;
    return checkbox;
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
    if (type !== 'danger') {
        setTimeout(function () {
            fadeOutAndRemoveAlert(alert);
        }, duration);
    }
}

function showErrorBar(cause, message) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `<div class="alert alert-danger alert-dismissible" role="alert">${cause}#&nbsp;${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>`;
    alertPlaceholder.append(wrapper);
    const alert = wrapper.firstElementChild;
    const closeButton = wrapper.querySelector('.btn-close');
    closeButton.onclick = function () {
        fadeOutAndRemoveAlert(alert);
    };
}

function fadeOutAndRemoveAlert(alert) {
    alert.classList.remove('show');
    setTimeout(() => alert.remove(), 150);
}


