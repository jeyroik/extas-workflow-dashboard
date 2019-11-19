<li class="list-group-item">
    <div class="row">
        <div class="col-md-3" title="@transition.description">@transition.title</div>
        <div class="col-md-3">
            @transition.state_from &rarr; @transition.state_to
        </div>
        <div class="col-md-3">
            <a href="/transitions/edit/@transition.name">Редактировать</a>
            <a href="/transitions/delete/@transition.name">Удалить</a>
        </div>
    </div>
</li>