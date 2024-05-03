export async function saveTemplate(store, msgPopup, isNew = false) {
    const endpoint = isNew ? `index.php?option=com_semantycanm&task=Template.update&id=` :
        `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(store.doc.id)}`;
    const method = 'POST';
    const data = {doc: store.doc};

    try {
        const response = await fetch(endpoint, {
            method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP error, status = ${response.status}`);
        }
        const result = await response.json();
        msgPopup.success(result.data.message);
    } catch (error) {
        msgPopup.error(error.message, {
            closable: true,
            duration: 10000
        });
    }
}

export async function deleteTemplate(store, msgPopup) {
    const endpoint = `index.php?option=com_semantycanm&task=Template.delete&id=${encodeURIComponent(store.doc.id)}`;
    try {
        const response = await fetch(endpoint, {
            method: 'DELETE'
        });

        if (!response.ok) {
            throw new Error(`Failed to delete template, HTTP status = ${response.status}`);
        }
        const result = await response.json();
        msgPopup.success(result.data.message);
        store.doc = {id: 0, name: '', type: '', description: '', content: '', wrapper: '', customFields: []};
    } catch (error) {
        msgPopup.error(error.message, {
            closable: true,
            duration: 10000
        });
    }
}
