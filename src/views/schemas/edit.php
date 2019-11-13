<div class="row">
    <div class="col-md-3">
        <div class="card">
            <form action="/schema/save/@schema.name" method="post">
                <div class="card-header">
                    <input name="title" type="text" class="form-control" value="@schema.title">
                </div>
                <div class="card-body">
                    <textarea name="description" class="form-control">@schema.description</textarea>
                </div>
                <div class="card-footer">
                    <input type="submit" value="Сохранить">
                </div>
            </form>
        </div>
    </div>
</div>