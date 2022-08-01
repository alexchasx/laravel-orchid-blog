<h4 id="-">ИСПРАВЛЕНИЕ НЕПОЛАДОК:</h4>
<ul>
    <li>Управление ролями</li>
    <li>Обновление статьи:
        1) автом-ки не устанавл-ся дата <code>published_at</code> (приходится вручную);</li>
    <li>Сделать валидацию при создании\обновлении статей в админке</li>
</ul>

<hr>

<h4 id="-">НОВЫЙ ФУНКЦИОНАЛ:</h4>
<ul>
    <li>Страница &quot;Логи&quot;</li>
    <li>Страница &quot;donate&quot; (Поддержать проект): функционал финансовой поддержки &quot;На развитие проекта + пожелания&quot;</li>
    <li>Альтернативный дизайн ?</li>
    <li>
        <p>Обязательно:</p>
        <ul>
            <li>Комментарии: защита от ботов или фраза &quot;Авторизуйтесь, чтобы комментировать&quot;</li>
            <li>Контакты: убрать почту; добавить форму + таблицу для сообщений</li>
        </ul>
    </li>
    <li>
        <p>обработка ошибок/исключений (по доке Laravel)?</p>
    </li>
    <li>картинки и аватарки?</li>
    <li>восстановление пароля?</li>
    <li>вывод ошибок от валидации AJAX!!!</li>
    <li>добавить лайки к постам и комментариям ??</li>
    <li>copyright (страница?)</li>
    <li>реализовать функционал &quot;Похожие комментарии&quot;</li>
    <li>реализовать вывод кол-ва статей при поиске</li>
    <li>показ имени аутентифицированного пользователя в админке и в блоге в хедере</li>
    <li>добавить альтернативную вёрстку с возможность переключения в конфигах</li>
    <li>альтернативная вёрстка с SPA (одностраничник)</li>
    <li>
        <p>даты как на Хабре</p>
    </li>
    <li>
        <p>АДМИНКА:</p>
        <ul>
            <li>валидация в админке создание рубрики, меток?</li>
            <li>пользователи: добавить столбец &quot;роль&quot;</li>
            <li>добавить альтернативную админку с возможность переключения в конфигах</li>
        </ul>
    </li>
</ul>

<hr>

<h4 id="-">ОПТИМИЗАЦИЯ И РЕФАКТОРИНГ:</h4>
<ul>
    <li>Комментарии: переделать на AJAX</li>
    <li>рефакторинг: валидация полей из форм</li>
    <li>рефакторинг. Тонкие контроллеры: шаблон репозиторий или сервисы</li>
    <li>кешировать данные sidebar на сервере</li>
    <li>оптимизировать запросы, убрав из выборки поле &quot;published&quot; ??</li>
    <li>count статей в рубриках и тегах заносить в таблицу в отдельн. колонку</li>
    <li>&quot;$this-&gt;middleware(&#39;auth&#39;)&quot; вместо моей проверки</li>
    <li>заменить findOrFail ??</li>
    <li>прижать футер?</li>
    <li>убрать фильтрацию по &quot;published_at&quot; и другие (вместо них планировщиком выставлять активность)</li>
    <li>убрать дублир.код (вывод рубрик и тегов) на каждой странице</li>
    <li>переписать экшены создания комментариев</li>
    <li>убрать поля Article: content_html, content_raw, excert</li>
</ul>

<hr>

<h4 id="-">СДЕЛАНО (протестить еще раз?):</h4>
<ul>
    <li>скопировать откуда-нибудь normalize.css (не reset.css)</li>
    <li>убрать секунды из дат или сделать их работающими</li>
    <li>сортировка статей на главной странице</li>
    <li>разделение ролей</li>
    <li>реализовать подсветку активной рубрики</li>
    <li>комментарии</li>
    <li>страница &quot;О блоге&quot;</li>
    <li>поиск</li>
    <li>убрать пустые теги (для статей с is_published=0)</li>
    <li>верстать страницу &quot;Контакты&quot;</li>
    <li>верстать форму комментария</li>
    <li>верстать блок комментариев</li>
    <li>аутентификация</li>
    <li>оформление пустой страницы</li>
    <li>править даты</li>
    <li>поиск статей по словам</li>
    <li>метки</li>
</ul>
