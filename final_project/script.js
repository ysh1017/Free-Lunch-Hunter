function validateForm() {
    const title = document.getElementsByName('title')[0].value;
    const content = document.getElementsByName('content')[0].value;
    const publishBtn = document.getElementsByName('publishBtn')[0];

    if (title && content) {
        publishBtn.classList.remove('disabled');
        publishBtn.disabled = false;
    } else {
        publishBtn.classList.add('disabled');
        publishBtn.disabled = true;
    }

    document.querySelector('.previewTitle').innerText = title;
    document.querySelector('.previewContent').innerText = content;
}

function publishArticle() {
    // 發佈文章的邏輯
    alert('文章已發佈');
}
