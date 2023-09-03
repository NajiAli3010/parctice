

# Tasks Web App 


Веб приложение представляет собой простое приложение с php laravel и vuejs, веб-приложение 
- это приложение tasks. пользователь может создать account в приложении, и затем он будет 
- перенаправлен на страницу, на которой отображаются 
tasks от других пользователей. сначала ему нужно выбрать, какую задачу он хочет создать, выбрав category и
- создать task или много tasks по своему усмотрению.


<details>
 <summary><h2>Используемый</h2></summary>

- Laravel
- Php 8
- Docker & docker compose
- VueJs
- Database MySQL
- Nginx
- Prometheus
- Node exporter
- Grafana

</details>


<details>
 <summary><h2>Github actions с уведомлениями telegram-бота </h2></summary>

1. я создал .github/workflows/build.yml для actions
2. 
```bash
   bash
   name: CI/CD & telegram actions
   on: [push]
   jobs:

build:
name: Build Push Docker Image
runs-on: ubuntu-latest
steps:
- uses: actions/checkout@master

      # Container Security Scanning
      - name: Install Trivy
        run: |
          wget https://github.com/aquasecurity/trivy/releases/download/v0.21.0/trivy_0.21.0_Linux-64bit.tar.gz
          tar zxvf trivy_0.21.0_Linux-64bit.tar.gz
          sudo mv trivy /usr/local/bin/


      - name: Build and Push Docker Image
        run: |
          docker-compose build
          echo ${{ secrets.DOCKERHUB_ACCESS_TOKEN }} | docker login -u ${{ secrets.DOCKERHUB_USERNAME }} --password-stdin
          docker-compose push
        env:
          DOCKER_BUILDKIT: 1


      - name: Scan Container Image
        run: trivy image ${{ secrets.DOCKERHUB_USERNAME }}/spa:latest\



      # Sending success or failure notifications to Telegram
      - name: Send Notification on Success
        if: success()
        uses: ./
        with:
          to: ${{ secrets.TELEGRAM_CHAT_ID }}
          token: ${{ secrets.TELEGRAM_TOKEN }}
          message: |
            ✅ **CI/CD Completed Successfully!**
            New push on branch: `${{ github.ref }}`
            Commit Message: `${{ github.event.head_commit.message }}`

      - name: Send Notification on Failure
        if: failure()
        uses: ./
        with:
          to: ${{ secrets.TELEGRAM_CHAT_ID }}
          token: ${{ secrets.TELEGRAM_TOKEN }}
          message: |
            ❌ **CI/CD Failed!**
            New push on branch: `${{ github.ref }}`
            Commit Message: `${{ github.event.head_commit.message }}`name: tasks github actions & telegram notifications
on: [push]
jobs:

  build:
    name: Build Push Docker Image
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@master


      - name: Build and Push Docker Image
        run: |
          docker-compose build
          echo ${{ secrets.DOCKERHUB_TOKEN }} | docker login -u ${{ secrets.DOCKERHUB_USERNAME }} --password-stdin
          docker-compose push
        env:
          DOCKER_BUILDKIT: 1



      # Container Security Scanning
      - name: Install Trivy
        run: |
          wget https://github.com/aquasecurity/trivy/releases/download/v0.21.0/trivy_0.21.0_Linux-64bit.tar.gz
          tar zxvf trivy_0.21.0_Linux-64bit.tar.gz
          sudo mv trivy /usr/local/bin/

      - name: Scan Container Image
        run: trivy image ${{ secrets.DOCKERHUB_USERNAME }}/tasks-app:latest\


      # Sending success or failure notifications to Telegram
      - name: Send Notification on Success
        if: success()
        uses: ./
        with:
          to: ${{ secrets.BOT_CHAT_ID }}
          token: ${{ secrets.BOT_TOKEN }}
          message: |
            ✅ CI/CD Completed Successfully!
            New push on branch: ${{ github.ref }}
            Commit Message: ${{ github.event.head_commit.message }}

      - name: Send Notification on Failure
        if: failure()
        uses: ./
        with:
          to: ${{ secrets.BOT_CHAT_ID }}
          token: ${{ secrets.BOT_TOKEN }}
          message: |
            ❌ CI/CD Failed!
            New push on branch: ${{ github.ref }}
            Commit Message: ${{ github.event.head_commit.message }}


```
## Workflow Overview

Рабочий процесс запускается автоматически всякий раз,
когда в репозитории обнаруживается новое push-событие.
Он состоит из следующих основных компонентов:

### Job: Build Push Docker Image

Это задание управляет всем процессом CI/CD
и состоит из нескольких ключевых этапов:

1. Code Checkout: Извлекается код репозитория, гарантирующий, что для последующих действий используется последняя версия.

2. Trivy Installation: Trivy, Trivy, надежный сканер уязвимостей для образов контейнеров, установлен в среде выполнения рабочего процесса.

3. Docker Image Build and Push: Образ Docker создается и впоследствии отправляется с помощью Docker Compose. Этот процесс включает в себя использование учетных данных Docker Hub, надежно хранящихся в виде секретов GitHub.

4. Image Vulnerability Scanning: Созданный образ Docker подвергается сканированию на уязвимости с помощью Trivy. Этот шаг помогает обеспечить сохранность изображения.

5. Notification on Success: Если процесс создания и отправки образа Docker завершается успешно, уведомление об успешном завершении отправляется на указанный канал Telegram. Уведомление содержит подробную информацию об успешном завершении CI/CD, новом push-событии и связанном с ним сообщении о фиксации.

6. Notification on Failure: В случае сбоя во время сборки образа Docker или push-процесса уведомление о сбое отправляется на тот же Telegram-канал. Уведомление сообщает о статусе сбоя процесса CI/CD вместе с соответствующей информацией о новом push-событии и соответствующем сообщении о фиксации.

## Как это работает

1. Всякий раз, когда в репозитории происходит новое push-событие, автоматически запускается рабочий процесс GitHub Actions.

2. Задание рабочего процесса координирует задачи создания, отправки и сканирования изображений Docker с помощью Docker Compose и Trivy.

3. В зависимости от результатов процесса CI/CD на указанный Telegram-канал отправляется соответствующее уведомление (об успехе или неудаче).

Внедрив этот рабочий процесс, я могу оптимизировать свой конвейер CI / CD,
повысить безопасность образа Docker за счет тщательного сканирования уязвимостей и
быть в курсе хода моих развертываний с помощью уведомлений Telegram.


2. я создал Dockerfile для github actions





```bash
FROM appleboy/drone-telegram:1.3.9-linux-amd64

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /github/workspace

ENTRYPOINT ["/entrypoint.sh"]

```

Этот Dockerfile разработан для использования в GitHub Actions и выполняет следующие действия:

1. Основной образ: appleboy/drone-telegram:1.3.9-linux-amd64.
2. Копирование файлов: Копирует содержимое каталога /var/www/html из образа php_stage в каталог /var/www/html внутри текущего образа.
3. Копирование `entrypoint.sh`: Перемещает файл entrypoint.sh в корневой каталог образа.
4. Назначение прав: Устанавливает исполняемые права на файл entrypoint.sh.
5. Рабочая директория: Устанавливает рабочую директорию как /github/workspace.
6. Точка входа: Задает точку входа для образа как /entrypoint.sh.

В результате данный Dockerfile настраивает образ для использования в GitHub Actions, осуществляя копирование файлов, настройку прав и определение точки входа для выполнения скрипта entrypoint.sh.



3. я создал entrypoint.sh file

```bash
#!/bin/sh
set -eu

export GITHUB="true"

[ -n "$*" ] && export TELEGRAM_MESSAGE="$*"

/bin/drone-telegram

```

Этот скрипт используется как точка входа в Docker-контейнере и предназначен для выполнения определенных действий:

1. Установка переменных окружения: Устанавливает переменную окружения GITHUB в значение true, указывая на выполнение в контексте GitHub.

2. Условное задание сообщения для Telegram: Если переданы аргументы командной строки, то значение переданных аргументов устанавливается в переменную окружения TELEGRAM_MESSAGE.

3. Запуск `/bin/drone-telegram`: Запускает исполняемый файл /bin/drone-telegram, выполняя последующие действия.

В итоге этот скрипт настраивает окружение, обрабатывает сообщение для Telegram (если указано), и запускает действия с использованием /bin/drone-telegram.

![Prometheus](public/Imgs/img9.jpg)
![Prometheus](public/Imgs/img1.jpg)
![Prometheus](public/Imgs/img2.jpg)
![Prometheus](public/Imgs/img3.jpg)
![Prometheus](public/Imgs/img4.jpg)
![Prometheus](public/Imgs/img6.jpg)
![Prometheus](public/Imgs/img7.jpg)
![Prometheus](public/Imgs/img8.jpg)
![Prometheus](public/Imgs/img5.jpg)


</details>



![Login Page](public/Imgs/tasks1.jpg)

![registration](public/Imgs/tasks3.jpg)

![registration](public/Imgs/tasks4.jpg)

![Login Page](public/Imgs/tasks5.jpg)

![registration](public/Imgs/tasks6.jpg)

![registration](public/Imgs/tasks7.jpg)



Prometheus -это система мониторинга и алертинга с открытым исходным кодом, 
разработанная для сбора и анализа метрик из различных источников. Он предоставляет 
возможности мониторинга, отслеживания и анализа производительности приложений, систем
и сетевых ресурсов.Прометей разработан для работы в распределенных средах и имеет модель сбора данных,
основанную на "отправителе-получателе". Он использует специальные агенты, называемые "экспортерами" (exporters),
чтобы собирать метрики из различных компонентов системы, таких как веб-серверы, базы данных, приложения и 
другие сервисы. Экспортеры преобразуют метрики в формат, понятный Прометею, и отправляют их на центральный
сервер Прометея.

Node Exporter -это инструмент с открытым исходным кодом, который собирает системные метрики
с узловых машин и предоставляет их в виде экспонируемых (экспортируемых) метрик в Прометей (Prometheus).
Узловой экспортер запускается на каждой машине, которую вы хотите мониторить, и собирает различные
метрики о системе, такие как использование ЦП, память, дисковое пространство, сетевые интерфейсы и 
другие характеристики узла. Он предоставляет эти метрики в Прометей, который сохраняет их во временном
ряду для последующего анализа и визуализации. Узловой экспортер работает по протоколу HTTP и предоставляет 
метрики в формате, который понимает Прометей. Прометей может собирать метрики с нескольких экспортеров 
Node Exporter в распределенной среде и использовать их для мониторинга и анализа состояния узловых машин.

Grafana (Графана) -это платформа для визуализации и мониторинга данных с открытым исходным кодом. 
Она позволяет создавать красивые и информативные графики, диаграммы и панели управления для 
отображения данных из различных источников.
Grafana поддерживает множество источников данных, включая базы данных, сервисы 
мониторинга (например, Прометей), системы журналирования и другие. Она обладает широким набором
возможностей визуализации, позволяя настраивать графики, добавлять аннотации, фильтровать данные 
и создавать интерактивные панели управления.
Одной из ключевых особенностей Grafana является ее гибкость. Она предоставляет широкий выбор панелей и
компонентов для создания настраиваемых и адаптивных дашбордов. Вы можете отображать несколько графиков и 
диаграмм на одной панели, настраивать цвета и стили, добавлять текстовые блоки и метки для лучшего понимания данных.



 <details>
 <summary><h2>Шаги</h2></summary>



1. показатели маршрута для конечной точки prometheus

```bash
Route::middleware([RequestsCountMiddleware::class])->group(function () {
    Route::get('/metrics',[PrometheusController::class, 'myMetrics']);
});

```


2. В классе PrometheusController я создал функцию моих metrics
```bash

class PrometheusController extends Controller
{
    public function myMetrics(Request $request)
    {
        DB::connection()->enableQueryLog();
        $collectorRegistry = app(CollectorRegistry::class);

        //memory usage metric
        $memoryUsage = memory_get_usage(true);
        $gauge = $collectorRegistry->getOrRegisterGauge('app', 'memory_usage_bytes', 'Memory usage in bytes');
        $gauge->set($memoryUsage);

        // Count the number of registered users
        $usersRegistered = User::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'users_registered_count',
            'Count the registered users'
        );
        $gauge->set($usersRegistered);


        // Count the number of created tasks
        $usersRegistered = Task::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'tasks_created_count',
            'Count the created tasks'
        );
        $gauge->set($usersRegistered);



        // Count the number of created categories
        $usersRegistered = Task::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'categories_created_count',
            'Count the created categories'
        );
        $gauge->set($usersRegistered);

        // Track cache hits
        $cacheHits = Cache::get('cache_hits', 0);
        $cacheHitsCounter = $collectorRegistry->getOrRegisterCounter(
            'tasks',
            'cache_hits_total',
            'count of cache hits'
        );
        $cacheHitsCounter->incBy($cacheHits);



        $renderer = new RenderTextFormat();
        $result = $renderer->render($collectorRegistry->getMetricFamilySamples());

        return response($result, 200)->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}

```
3.я создал middleware для metrics запросов

```bash
 
class RequestsCountMiddleware
{
    private CollectorRegistry $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @throws MetricsRegistrationException
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        $requestCounter = $this->registry->getOrRegisterCounter(
            'tasks',
            'request_count',
            'Count the number of requests',
            ['path', 'method', 'status_code']
        );
        $requestCounter->incBy(1, [$path, $method, (string) $statusCode]);

        return $response;
    }
}
```

4. Dockerfile
```bash 
FROM php:8.2.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    curl \
    && docker-php-ext-install zip pdo_mysql pdo_pgsql

# Install GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install -y nodejs

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Set max_execution_time
RUN echo "php_value[max_execution_time] = 120" >> /usr/local/etc/php/conf.d/docker-php-max-execution-time.ini

# Install PHP dependencies
RUN composer install

# Install Node.js dependencies and build assets
RUN npm install

```

5. docker-compose.yml контейнеры (tasks , database ,nginx , prometheus , node-exporter, grafana)

```bash
version: '3.8'

networks:
    default:
        external: true
        name: tasks_default

services:
    tasks:
        build:
            context: .
            dockerfile: Dockerfile
        container_name: tasks
        volumes:
            - .:/var/www/html
        depends_on:
            - database
            - prometheus

    nginx:
        image: nginx:latest
        container_name: nginx
        ports:
            - '8000:8000'
        volumes:
            - .:/var/www/html
            - ./Monitor/nginx.conf:/etc/nginx/conf.d/default.conf
        depends_on:
            - tasks


    database:
        image: mysql:8.0
        container_name: database
        ports:
            - '3306:3306'
        environment:
            MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
            MYSQL_ROOT_HOST: "%"
            MYSQL_DATABASE: '${DB_DATABASE}'
            MYSQL_PASSWORD: '${DB_PASSWORD}'
        volumes:
            - tasks_mysql_data:/var/lib/mysql

    prometheus:
        image: prom/prometheus
        container_name: prometheus
        ports:
            - "9090:9090"
        volumes:
            - ./Monitor:/etc/Monitor
            - ./Monitor/scrape_config.yml:/etc/Monitor/scrape_config.yml
        command:
            - --config.file=/etc/Monitor/scrape_config.yml
        depends_on:
            - node-exporter


    node-exporter:
        image: prom/node-exporter
        container_name: node-exporter
        ports:
            - "9100:9100"


    grafana:
        image: grafana/grafana
        container_name: grafana
        ports:
            - "3000:3000"
        depends_on:
            - prometheus


```


6. Node Exporter Configuration
```bash
# MySQL Exporter Configuration
[client]
user=root
password=naji123
host=mysql
database=tasks

# MySQL Exporter Collectors
collect[] = "status"
collect[] = "processlist"
collect[] = "performance_schema.events_statements_summary_by_digest"
collect[] = "performance_schema.events_statements_summary_global_by_digest"
collect[] = "performance_schema.events_waits_summary_global_by_event_name"
collect[] = "performance_schema.file_summary_by_event_name"
collect[] = "performance_schema.table_io_waits_summary_by_table"
collect[] = "performance_schema.table_lock_waits_summary_by_table"
collect[] = "performance_schema.table_lock_waits_summary_by_table"
collect[] = "performance_schema.table_statistics"
collect[] = "performance_schema.index_statistics"


```

7. nginx Configuration
```bash
server {
    listen 8000;
    index index.php index.html;
    root /var/www/html/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass tasks:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}


```

8.  Node Exporter and Prometheus Configuration
```bash
global:
  scrape_interval: 15s
  scrape_timeout: 10s

scrape_configs:
  - job_name: 'nginx'
    metrics_path: '/metrics'
    static_configs:
      - targets: ['nginx:8000']

  - job_name: 'node-exporter'
    static_configs:
      - targets: [ 'node-exporter:9100' ]


```
</details>


<details>
  <summary><h2>контейнеры docker</h2></summary>

![Login Page](public/Imgs/docker.jpg)

</details>



<details>
  <summary><h2>убедитесь, что веб-приложение получает metrics в route localhost:8000/metrics</h2></summary>

![Login Page](public/Imgs/tasks7.jpg)

</details>

<details>
  <summary><h2>Prometheus</h2></summary>

![Prometheus](public/Imgs/promethesu1.jpg)
![Prometheus](public/Imgs/promethesu2.jpg)
![Prometheus](public/Imgs/promethesu3.jpg)
![Prometheus](public/Imgs/promethesu4.jpg)
![Prometheus](public/Imgs/promethesu5.jpg)
![Prometheus](public/Imgs/promethesu6.jpg)
![Prometheus](public/Imgs/promethesu7.jpg)



</details>

<details>
  <summary><h2>Node exporter </h2></summary>

![mysql-exporter](public/Imgs/node1.jpg)
![mysql-exporter](public/Imgs/node2.jpg)
</details>

<details>
  <summary><h2>Grafana</h2></summary>

![grafana ](public/Imgs/grafana1.jpg)
![grafana ](public/Imgs/grafana2.jpg)
![grafana ](public/Imgs/grafana3.jpg)
![grafana ](public/Imgs/grafana4.jpg)
![grafana ](public/Imgs/grafana5.jpg)
![grafana ](public/Imgs/grafana6.jpg)
![grafana ](public/Imgs/grafana7.jpg)
![grafana ](public/Imgs/grafana8.jpg)
![grafana ](public/Imgs/grafana9.jpg)
</details>
