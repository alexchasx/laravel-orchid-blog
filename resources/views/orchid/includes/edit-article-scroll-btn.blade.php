<style>
/* Sticky-кнопка «Применить» внизу модалки редактирования статьи */
#edit-article-sticky-btn {
    position: sticky;
    bottom: 0;
    z-index: 1050;
    display: block;
    width: 100%;
    padding: 10px 16px;
    margin: 0;
    border: none;
    border-top: 1px solid #dee2e6;
    background: #0259fa;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.08);
    font-size: 14px;
    font-weight: 600;
    border-radius: 0;
}

html.dark #edit-article-sticky-btn {
    background: #1e2125;
    border-top-color: #373a3e;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.3);
    color: #fff;
}

#edit-article-sticky-btn:hover {
    /* opacity: 0.9; */
    background: #7da9f9;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    /*
     * Sticky-кнопка «Применить» внизу модалки
     * «Редактирование статьи». Дублирует функцию сохранения формы.
     */
    function addStickyButton() {
        var modal = document.querySelector("#screen-modal-editArticle.modal");
        if (!modal) return;

        // Если кнопка уже есть — ничего не делаем
        if (modal.querySelector('#edit-article-sticky-btn')) return;

        // Кнопка «Применить»
        var btn = document.createElement("button");
        btn.type = "submit";
        btn.id = "edit-article-sticky-btn";
        btn.textContent = "Применить";
        btn.title = "Сохранить изменения";

        // Вставляем в конец body модалки (перед закрывающим тегом)
        modal.querySelector('.modal-body').appendChild(btn);
    }

    // Наблюдаем за появлением и показом модалки
    var observer = new MutationObserver(function () {
        addStickyButton();
    });
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>
