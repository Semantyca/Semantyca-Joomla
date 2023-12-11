function dragAndDropSet(sourceList, targetGroup) {
    let body = document.body;

    function onStart(evt, target) {
        evt.item.classList.add("dragging");
        target.classList.add("drop-area-highlight");
        body.style.cursor = 'grabbing';
    }

    function onEnd(evt, target, isSource) {
        evt.item.classList.remove("dragging");
        target.classList.remove("drop-area-highlight");
        body.style.cursor = '';

        let draggedElement = evt.item;
        let duplicate = Array.from(target.children).some(li => {
            return li.dataset.id === draggedElement.dataset.id;
        });

        if (!duplicate) {
            if (isSource) {
                target.appendChild(draggedElement);
            } else {
                let sourceDuplicate = Array.from(sourceList.children).some(li => {
                    return li.dataset.id === draggedElement.dataset.id;
                });
                if (!sourceDuplicate) {
                    sourceList.appendChild(draggedElement);
                }
            }
        }
    }

    Sortable.create(sourceList, {
        group: {
            name: 'shared',
            pull: true,
            put: true
        },
        animation: 150,
        sort: false,
        onStart: function (evt) {
            onStart(evt, targetGroup);
        },
        onEnd: function (evt) {
            onEnd(evt, targetGroup, true);
        }
    });

    Sortable.create(targetGroup, {
        group: {
            name: 'shared',
            pull: true,
            put: true
        },
        animation: 150,
        sort: false,
        onStart: function (evt) {
            onStart(evt, sourceList);
        },
        onEnd: function (evt) {
            onEnd(evt, sourceList, false);
        }
    });
}
