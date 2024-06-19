// 以下為添加動態背景效果的代碼
document.addEventListener('scroll', function () {
    const posts = document.querySelectorAll('.post');
    posts.forEach((post, index) => {
        const speed = 0.5 + (index * 0.1);
        post.style.backgroundPositionY = `${window.scrollY * speed}px`;
    });
});
