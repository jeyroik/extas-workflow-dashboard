<div class="card">
    <div class="card-header">@state.title</div>
    <div class="card-body">
        @state.description
    </div>
    <div class="card-footer">
        <form action="/states/edit/@state.name">
            <input class="btn btn-primary" type="submit" value="Редактировать">
        </form>
        <form action="/states/delete/@state.name">
            <input class="btn btn-danger" type="submit" value="Удалить">
        </form>
    </div>
</div>