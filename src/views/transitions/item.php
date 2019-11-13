<div class="card">
    <div class="card-header">@transition.title</div>
    <div class="card-body">
        @transition.description
    </div>
    <div class="card-footer">
        <form action="/transitions/edit/@transition.name">
            <input class="btn btn-primary" type="submit" value="Редактировать">
        </form>
        <form action="/transitions/delete/@transition.name">
            <input class="btn btn-danger" type="submit" value="Удалить">
        </form>
    </div>
</div>