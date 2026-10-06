<style>
/* Счётчик символов для поля «Мета деск» (meta_desc) в модалках статей. */
.js-meta-desc-counter {
    font-size: 12px;
    color: #6c757d !important;
}

/* Превышен рекомендуемый лимит (150 символов). */
.js-meta-desc-counter.is-over {
    color: #dc3545 !important;
    font-weight: 600;
}

html.dark .js-meta-desc-counter {
    color: #adb5bd !important;
}

html.dark .js-meta-desc-counter.is-over {
    color: #ff6b6b !important;
}
</style>

<script>
(function () {
    'use strict';

    var LIMIT = 150;
    var FIELD = 'input[name="article[meta_desc]"]';
    var COUNTER = '.js-meta-desc-counter';

    /*
     * Обновляет счётчик для конкретного input.
     * Счётчик лежит в том же .form-group, что и поле.
     */
    function update(input) {
        var group = input.closest('.form-group') || document;
        var counter = group.querySelector(COUNTER);
        if (!counter) return;

        var len = input.value.length;
        counter.textContent = len + ' / ' + LIMIT;
        counter.classList.toggle('is-over', len > LIMIT);
    }

    function initAll(root) {
        (root || document).querySelectorAll(FIELD).forEach(update);
    }

    /*
     * Делегирование события input: работает и для полей,
     * добавленных динамически (async-модалка «Редактирование»).
     */
    document.addEventListener('input', function (event) {
        var target = event.target;
        if (target && target.matches && target.matches(FIELD)) {
            update(target);
        }
    });

    /* Пересчёт при показе модалки (Bootstrap-событие). */
    document.addEventListener('shown.bs.modal', function () {
        initAll(document);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAll(document);
        });
    } else {
        initAll(document);
    }

    /*
     * Страховка для async-загрузки содержимого модалки (Turbo stream):
     * реагируем только на добавленные элементы, чтобы не зациклиться
     * на собственных изменениях текста счётчика.
     */
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!node || node.nodeType !== 1) return;
                if (node.matches && node.matches(FIELD)) {
                    update(node);
                }
                if (node.querySelectorAll) {
                    node.querySelectorAll(FIELD).forEach(update);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
})();
</script>
