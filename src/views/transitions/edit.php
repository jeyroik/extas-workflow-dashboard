<li class="list-group-item">
    <form action="/transitions/save/@transition.name" method="post">
        <div class="row">
            <div class="col-md-6">
                <input title="Название" placeholder="Название" class="inline-input form-control" name="title" type="text" value="@transition.title"/>
                <input title="Описание" placeholder="Описание" class="inline-input form-control" name="description" type="text" value="@transition.description"/>
            </div>
            <div class="col-md-3">
                <input title="Из статуса" placeholder="Из статуса" class="inline-input form-control" name="state_from" type="text" value="@transition.state_from"/> &rarr;
                <input title="В статус" placeholder="В статус" class="inline-input form-control" name="state_to" type="text" value="@transition.state_to"/>
            </div>
            <div class="col-md-3">
                <input type="submit" value="Сохранить" class="btn btn-primary">
            </div>
        </div>
    </form>
</li>