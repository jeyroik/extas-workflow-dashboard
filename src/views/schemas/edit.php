<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="card">
            <div class="card-header">
                <a href="/">Схемы</a> <a href="/states"> Состояния</a> <a href="/transitions"> Переходы</a>
            </div>
            <div class="card-body">
                <div class="card">
                    <form action="/schemas/save/@schema.name" method="post">
                        <div class="card-header">
                            <input name="title" type="text" class="form-control" value="@schema.title">
                        </div>
                        <div class="card-body">
                            <textarea name="description" class="form-control">@schema.description</textarea>
                        </div>
                        <div class="card-body">
                            <textarea name="transitions" class="form-control">@schema.transitions</textarea>
                        </div>
                        <div class="card-body">
                            @schema.entity_name
                        </div>
                        <div class="card-footer">
                            <input type="submit" value="Сохранить">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>