# extas-workflow-dashboard

Простой (но функциональный) микросервис для построения Workflow (бизнес процесса) и прогона сущности по нему.

Текущая версия включает в себя:
- JSON-RPC API со следующими операциями (все спецификации доступны по /specs/ - см. Использование):
    - Список состояний
    - Создание состояния
    - Обновление состояния
    - Удаление состояния
    - Загрузка списка состояний
    - Список переходов
    - Создание перехода
    - Обновление перехода
    - Удаление перехода
    - Загрузка списка переходов
    - Список переходов по исходному состоянию
    - Список шаблонов обработчиков переходов
    - Создание шаблона обработчика перехода
    - Обновление шаблона обработчика перехода
    - Удаление шаблона обработчика перехода
    - Список обработчиков перехода
    - Создание обработчика перехода
    - Обновление обработчика перехода
    - Удаление обработчика перехода
    - Список шаблонов сущности
    - Создание шаблона сущности
    - Обновление шаблонов сущности
    - Удаление шаблонов сущности
    - Список схем
    - Создание схемы
    - Обновление схемы
    - Удадение схемы
    - Добавление существующего перехода в схему
    - Удаление перехода из схемы
    - Применение к сущности перехода
- Web-панель управления, которая на текущий момент позволяет:
    - Просматривать схемы workflow (отображаются графами).
    - Редактировать схему workflow (добавлять, убирать переходы; менять заголовок, описание и шаблон сущности).
    - Просматривать список состояний.
    - Создавать состояние.
    - Редактировать состояние (заголовок и описание).
    - Просматривать список переходов.
    - Создавать переход.
    - Редактировать переход (заголовок, описание, исходное и конечное состояния).

# Установка

`composer require jeyroik/extas-workflow-dashboard:*`

```
# vendor/bin/extas i
# vendor/bin/extas jsonrpc -s resources/extas.json
# vendor/bin/extas i
```


# Использование

`# php -S 0.0.0.0:8080 -t vendor/jeyroik/extas-jsonrpc/src/public`

После запуска при переходе по localhost:8080 должен отобразиться экран "Схемы".

Также после запуска становится доступной json-rpc API по адресу localhost:8080/api/jsonrpc.

Спецификации операций можно посмотреть по адресу localhost:8080/specs/:

```
POST localhost:8080/specs/
Body:
{
    "method": "operation.all",
    "id": "<uuid4, но сейчас принимается любой, т.к. ни на что не влияет>"
}
```

# Release notes

- (json-rpc) Добавить во все методы со списками поддержку фильтров.
- (json-rpc) Добавить во все методы со списками поддержку сортировки. 