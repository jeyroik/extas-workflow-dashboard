<li class="list-group-item">
    <form action="/states/save/@state.name" method="post">
        <div class="row">
            <div class="col-md-6">
                <input class="inline-input form-control" name="title" type="text" value="@state.title"/>
                <input class="inline-input form-control" name="description" type="text" value="@state.description"/>
            </div>
            <div class="col-md-3">
                <input type="submit" value="Сохранить" class="btn btn-primary">
            </div>
        </div>
    </form>
</li>