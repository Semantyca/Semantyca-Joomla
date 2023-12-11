document.getElementById('addRecord').addEventListener('click', function() {
    var name = prompt("Enter the name of the new record:");

    if (name) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_semantycanm&task=mailinglist.addRecord');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('Record added successfully');
                location.reload();
            }
            else {
                alert('An error occurred');
            }
        };
        xhr.send(encodeURI('name=' + name));
    }
});

document.getElementById('removeRecord').addEventListener('click', function() {
    var checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    var ids = Array.from(checkboxes).map(function(checkbox) {
        return checkbox.value;
    });

    if (ids.length > 0) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_semantycanm&task=mailinglist.removeRecords');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('Records removed successfully');
                location.reload();
            }
            else {
                alert('An error occurred');
            }
        };
        xhr.send(encodeURI('ids=' + ids.join(',')));
    }
});
