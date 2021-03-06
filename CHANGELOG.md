# JohnCMS 7.1.0  
`Release Date: 07.04.2017`

### NEW
- Добавлен пакет интерфейсов Core API
- Использование [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) интерфейса контейнера
- Объединение идущих подряд постов форума от одного автора
- Возможность индивидуального удаления прикрепленных файлов форума
- BBcode для вставки Youtube видео (доработанный метод от [Gabriel](https://johncms.com/profile/?user=38235))
- Добавлен Украинский язык
- Добавлен Литовский язык
- Добавлен Арабский язык
  
### CHANGED
- Доработан класс BBcode
- Доработаны пользовательские настройки
- Добавлено 10 секундное кэширование счетчика Онлайн
- Обновлены библиотеки зависимостей

### FIXED
- Исправлена ошибка SQL при обновлении директорий загруз центра
- Исправлена ошибка SQL при очистке неактивных профилей
- Исправлен SQL запрос добавления новостей на форум
- Исправлен вывод аннонсов новостей на Главную
- Исправлен счетчик комментариев в альбомах
  
### DELETED
- Nothing

------------------------------------------------------------

# JohnCMS 7.0.0  
`Release Date: 22.01.2017`

### NEW
- Новое ядро системы, построенное на DI контейнере zend-servicemanager
- Для работы с базой данных используется PDO
- Для локализации (переводов) используется формат GNU Gettext  
- Новый загруз центр от [FlySelf](https://johncms.com/profile/?user=130) с доработками от [AlkatraZ](https://johncms.com/profile/?user=1)
- Поддержка шифрованного протокола HTTPS
  
### CHANGED
- Полный рефакторинг всех скриптов системы
- Все подсистемы разделены на отдельные модули
- Системная конфирурация поступает из файла через контейнер, таблица базы удалена
- Папки локалей переименованы в соответствие со стандартом [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
- Снято ограничение на длину и символы паролей
- Доработана справочная система

### FIXED
- В Библиотеке исправлена скачка книг в формате FB
- Исправлены некоторые замеченные ошибки
  
### DELETED
- Удален устаревший модуль "Загрузки"
- Удален устаревший модуль "Галерея"
- Удален устаревший модуль "Друзья"
- Удалена карта сайта, предназначенная для бирж ссылок
- Удален валидатор Referer
- Удален редактор языков
- Удален транслит
