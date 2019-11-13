<div class="card">
    <div class="card-header">@schema.title</div>
    <div class="card-body">
        @schema.description
    </div>
    <div class="card-body">
        @schema.transitions
    </div>
    <div class="card-footer">
        <form action="/schema/edit/@schema.name">
            <input class="btn btn-primary" type="submit" value="Редактировать">
        </form>
        <form action="/schema/delete/@schema.name">
            <input class="btn btn-danger" type="submit" value="Удалить">
        </form>
    </div>
</div>