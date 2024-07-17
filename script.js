document.getElementById('moreLinksButton').addEventListener('click', function() {
    var moreLinks = document.getElementById('moreLinks');
    if (moreLinks.classList.contains('hidden')) {
        moreLinks.classList.remove('hidden');
    } else {
        moreLinks.classList.add('hidden');
    }
});