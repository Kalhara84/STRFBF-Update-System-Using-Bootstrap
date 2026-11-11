/* Match the inventory type textbox to its existing select options. */
function autoComplete(input, select, property, complete) {
    if (!input || !select) return;
    var query = input.value.toLowerCase();
    var event = window.event;
    var key = event && (event.key || event.keyCode);
    if (key === 'Backspace' || key === 'Delete' || key === 8 || key === 46) return;
    if (!query) return;
    for (var i = 0; i < select.options.length; i++) {
        var label = String(select.options[i][property || 'text']);
        if (label.toLowerCase().indexOf(query) === 0) {
            select.selectedIndex = i;
            if (complete) {
                input.value = label;
                if (input.setSelectionRange) input.setSelectionRange(query.length, label.length);
            }
            return;
        }
    }
}
