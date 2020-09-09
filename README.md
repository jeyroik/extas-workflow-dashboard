![PHP Composer](https://github.com/jeyroik/extas-workflow-dashboard/workflows/PHP%20Composer/badge.svg?branch=master&event=push)
![codecov.io](https://codecov.io/gh/jeyroik/extas-workflow-dashboard/coverage.svg?branch=master)
<a href="https://github.com/phpstan/phpstan"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a>
<a href="https://codeclimate.com/github/jeyroik/extas-workflow-dashboard/maintainability"><img src="https://api.codeclimate.com/v1/badges/fcade60c962fb84e49e2/maintainability" /></a>
[![Latest Stable Version](https://poser.pugx.org/jeyroik/extas-workflow-dashboard/v)](//packagist.org/packages/jeyroik/extas-workflow-dashboard)
[![Total Downloads](https://poser.pugx.org/jeyroik/extas-workflow-dashboard/downloads)](//packagist.org/packages/jeyroik/extas-workflow-dashboard)
[![Dependents](https://poser.pugx.org/jeyroik/extas-workflow-dashboard/dependents)](//packagist.org/packages/jeyroik/extas-workflow-dashboard)

# Описание

Простой (но функциональный) микро-сервис для построения Workflow (бизнес процесса) и прогона сущности по нему.

# Установка

`composer require jeyroik/extas-workflow-dashboard:*`

```
# vendor/bin/extas init
# vendor/bin/extas install
# vendor/bin/extas jsonrpc -s resources/extas.json
# vendor/bin/extas install
```


# Использование

`# php -S 0.0.0.0:8080 -t vendor/jeyroik/extas-api/src/public`

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

- (json-rpc) Добавить во все методы со списками поддержку сортировки. 