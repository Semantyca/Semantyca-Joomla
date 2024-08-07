/*
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

function startLoading(loadingSpinnerId) {
    document.getElementById(loadingSpinnerId).style.visibility = 'visible';
}

function stopLoading(loadingSpinnerId) {
    document.getElementById(loadingSpinnerId).style.visibility = 'hidden';
}


function showAlertBar(message, type = 'danger', duration = 5000) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    const messageTextNode = document.createTextNode(message);
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade`;
    alertDiv.role = 'alert';
    alertDiv.appendChild(messageTextNode);
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.onclick = function () {
        fadeOutAndRemoveAlert(alertDiv);
    };
    alertDiv.appendChild(closeButton);
    wrapper.appendChild(alertDiv);
    alertPlaceholder.appendChild(wrapper);
    setTimeout(() => alertDiv.classList.add('show'), 10);
    if (type !== 'danger') {
        setTimeout(function () {
            fadeOutAndRemoveAlert(alertDiv);
        }, duration);
    }
}

function showErrorBar(cause, message) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    const messageTextNode = document.createTextNode(`${cause}# ${message}`);
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible';
    alertDiv.role = 'alert';
    alertDiv.appendChild(messageTextNode);
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.setAttribute('data-bs-dismiss', 'alert');
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.onclick = function () {
        fadeOutAndRemoveAlert(alertDiv);
    };
    alertDiv.appendChild(closeButton);
    wrapper.appendChild(alertDiv);
    alertPlaceholder.appendChild(wrapper);
}


function fadeOutAndRemoveAlert(alert) {
    alert.classList.remove('show');
    setTimeout(() => alert.remove(), 150);
}


*/
