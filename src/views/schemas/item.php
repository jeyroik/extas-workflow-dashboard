<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div id="container_@schema.name"></div>
            </div>
        </div>
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