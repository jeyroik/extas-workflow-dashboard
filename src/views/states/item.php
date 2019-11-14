<li class="list-group-item">@state.title (@state.description)
    <form action="/states/edit/@state.name">
        <input class="btn btn-primary" type="submit" value="Редактировать">
    </form>
    <form action="/states/delete/@state.name">
        <input class="btn btn-danger" type="submit" value="Удалить">
    </form>
</li>