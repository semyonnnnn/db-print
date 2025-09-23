# Database Structure Documentation

## 1. dbo.dtproperties

- [id, objectid, property, value, uvalue, lvalue, version]  
  (System table, metadata for diagrams in SQL Server)

---

## 2. dbo.fd\_<7 digits number>

- munr (8 digits) → Муниципальный номер
- tippos (1–2 digits) → Тип поселения
- oktmo (8 digits) → Код ОКТМО (территориальный классификатор)
- torgest (1–3 digits) → Торговая единица / хозяйственный субъект
- god (4 digits) → Год
- period (3 digits) → Период
- nomsob (5 digits) → Номер события
- zn<7dn> (floats, имя = название показателя) → Значение показателя

---

## 3. dbo.p\_<text>

### 3.1 dbo.p\_<text>

- kodzpr (2–3 digits) → Код запроса
- nomzpr (2–3 digits) → Номер запроса
- namezpr (text ru) → Название запроса

### 3.2 dbo.p\_<text>\_fd (optional)

- namefd (7 digits) → Имя поля данных
- kodzpr (2–3 digits) → Код запроса (связь с таблицей выше)

---

## 4. dbo.s\_\* (справочники)

### 4.1 dbo.s_ei

-
- kodei (1–4 digits) → Код единицы измерения
- grei (1–3 digits, NULL) → Группа единиц
- koefei (1–4 digits, коэффициент, 1e-06, decimals) → Коэффициент единицы
- nameei (text ru) → Название единицы измерения

### 4.2 dbo.s_ist

- istpok → Код источника показателя
- nameist → Название источника

### 4.3 dbo.s_pok

- kodpok (1–7 digits) → Код показателя
- kodksp (NULL / 1–2 / 7 digits) → Код классификатора (внешний ключ)
- kodei (NULL / 3 digits) → Код единицы измерения
- vidpok (NULL / number 1) → Вид показателя
- istpok (NULL / number 1) → Источник показателя
- tippok (NULL / number 1) → Тип показателя
- nompok (2–5 digits) → Номер показателя
- maxzpok (NULL / 15) → Максимальное значение
- kzpok (NULL / 4) → Код записи
- kodpolz (NULL / 4 digits, 1000/3000/9001) → Код пользователя, добавившего показатель
- datekor (date or null) → Дата корректировки
- datevst (date or null) → Дата вставки записи
- namefd (empty or 7 digits) → Имя поля фонда данных
- namepole (NULL / zn<7dn>) → Имя поля (колонки)
- namepok (text ru) → Наименование показателя
- opispok (empty or NULL) → Описание показателя

### 4.4 dbo.s_pok_prizn

- kodpok (7 digits) → Код показателя
- namesprav (text: munr, tippos, oktmo, uslug, god, period) → Имя справочника (признак)
- nomprfd (1–2 digits, e.g. 1–7, 10, 20, 30...) → Номер признака фонда данных

### 4.5 dbo.s_pok_v

- kodpokn (7 digits) → Основной показатель
- kodpokv (1–2 digits) → Связанный показатель
- nompokv (1–4 digits) → Номер связи

### 4.6 dbo.s_pokdostup

- kodpolz (1000, 3000, 9001) → Код пользователя
- kodpok (7 digits, реже 1–2) → Код показателя
- viddostup (always 3) → Вид доступа

### 4.7 dbo.s_pokmeta

- kodpok
- nommeta
- namemeta
  (все пустые)

### 4.8 dbo.s_prizn

- namesprav (munr, oktmo, period, uslug, obroz, zdrav, …) → Кодовое имя справочника
- nameprizn (text ru) → Читаемое название признака
- przpr (null or 0)
- kodtipzpr (only 4) → Связь с типом запроса

### 4.9 dbo.s_raz

- kodraz (1) → Код разреза
- namesprav ("nomer")
- nameraz ("Всего")
- nomraz (NULL)

### 4.10 dbo.s_sob

- nomsob (1–4 digits) → Номер события
- kodpolz (1000/9001) → Код пользователя
- datasob (date) → Дата события
- vidsob (1 or 2) → Вид события

### 4.11 dbo.s_tip

- tippok
- nametip
  (оба пустые)

### 4.12 dbo.s_tipzpr

- kodtipzpr (1,2,4,8) → Код типа запроса
- dlina (1,2,4,6) → Длина числа
- nametipzpr (tinyint, smallint, int, bigint) → Тип данных

### 4.13 dbo.s_vid

- vidpok
- namevid
  (оба пустые)

### 4.14 dbo.s_viddostup

- viddostup (1,2,3) → Код доступа
- namedostup ("Корректировка фонда данных", "Чтение фонда данных", "Корректировка справочников")

### 4.15 dbo.s_vidsob

- vidsob (-2..8) → Код вида события
- namesob (text: "Добавить число", "Изменить число", "Агрегирование", …)

---

## 5. dbo.temp\_\* (временные таблицы)

### 5.1 dbo.temp_fd

- munr (8 digits) → Муниципальный номер
- tippos (1–2 digits) → Тип поселения
- oktmo (8 digits) → Код территории
- okved2 (3–5 digits) → Код ОКВЭД2
- god (4 digits) → Год
- period (2 digits) → Период
- nomsob (5 digits) → Номер события
- zn<7dn> (1–dn) → Значение

### 5.2 dbo.temp_fd2 / temp_fd8

Структура аналогична, отличается длиной period и nomsob.

---

## 6. dbo.wr_tab2

- im (oktmo) → Код территории
- id (8–10 digits) → Идентификатор
- name (fd\_<7dn>) → Имя фонда данных
- kol_z (0 or 1–4) → Количество значений / признак
