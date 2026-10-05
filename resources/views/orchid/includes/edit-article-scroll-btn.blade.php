<script>
document.addEventListener("DOMContentLoaded", function () {
    var observer = new MutationObserver(function () {
        var modal = document.querySelector("#screen-modal-editArticle.modal");
        if (modal && !modal.querySelector(".scroll-bottom-btn")) {
            var btn = document.createElement("button");
            btn.type = "button";
            btn.className = "scroll-bottom-btn btn btn-outline-secondary btn-sm mt-2 mb-2";
            btn.innerHTML = "\u2193 Вниз";
            btn.title = "Пролистать модалку в самый низ";
            btn.onclick = function () {
                var body = modal.querySelector(".modal-body");
                if (body) body.scrollTop = body.scrollHeight;
            };
            var contentField = modal.querySelector("[name='article.content_raw']");
            if (contentField) {
                contentField.parentNode.insertBefore(btn, contentField);
            } else {
                modal.querySelector(".modal-body").appendChild(btn);
            }
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>
