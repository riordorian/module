<?php
/**
 *  module
 *
 * @category    
 * @author      dadaev@.com
 * @link        http://.ru
 */

namespace Site\Main;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Класс для реализации импортов
 */
class Import
{

    /**
     * Управление данным отправленными из форм администранивного скрипта xml-импорта
     * Определяет какой импорт на данный момент запускается и передает данные в методы, решающие более узкие задачи
     *
     * @param array $requestData
     * @return string
     */

    public function importRequsetManage($requestData)
    {
        if ($requestData['work_start']) {
            if (!$requestData["lastIndex"] && $_SESSION[$requestData["service"]]) {
                unset($_SESSION[$requestData["service"]]);
            }
            if (
                $_SESSION[$requestData["service"]] &&
                $_SESSION[$requestData["service"]]["sessid"] == $requestData["sessid"]
            ) {
                // получение данных
                $importObject = $_SESSION[$requestData["service"]];
            } else {
                // получение объекта из выгрузки клиента
                $importObject = self::getImportObject($requestData["service"], $requestData["sessid"]); //
            }
            $lastIndex = intval($requestData["lastIndex"]);
            $limit = intval($requestData["limit"]);
            $el = new \CIBlockElement;
            for ($index = $lastIndex; $index < $lastIndex + $limit; $index++) {
                // непсредственно сам импорт
                self::importElement($importObject[$index], $requestData["service"]);
            }
            // формирование статусной строки
            $lastIndex += $limit;
            $leftBorderCnt = $lastIndex;
            $allCnt = count($importObject);
            if ($importObject["sessid"]) {
                $allCnt--;
            }
            $p = round(100 * $leftBorderCnt / $allCnt, 2);
            // импорт закончился - сбрасываем объект из сессии
            if ($lastIndex == $allCnt) {
                self::importEpilog($requestData["service"]);
                unset($_SESSION[$requestData["service"]]);
            }
            $elementIndexed = $lastIndex - 1;
            if ($importObject[$elementIndexed]["type"] == "section") {
                $elementType = Loc::getMessage("site_MAIN_IMPORT_SECTION");
            } else {
                $elementType = Loc::getMessage("site_MAIN_IMPORT_ELEMENT");
            }
            $key = $importObject[$elementIndexed]["name"] ? $importObject[$elementIndexed]["name"] : $importObject[$elementIndexed]["NAME"];
            if ($key) {
                $indexResult = $p < 100 ? '&lastIndex=' . $lastIndex : '';
                $returnResult = json_encode(
                    array(
                        $p, $indexResult,
                        Loc::getMessage("site_MAIN_IMPORT_ACTIVITY") . $elementType . " - " . $key, $requestData["service"]
                    )
                );
                return $returnResult;
            }
        }
    }

    /**
     * Получение объекта из выгрузки клиента
     *
     * Есть 2 важные особенности:
     * Название элемента обязательно должно содержаться в ключе name
     *
     * @param string $service код сервиса
     * @param string $sessID id сессии
     * @return array $importObject Массив элементов импорта. Обязательно долден быть проиндексированным (как в примере)
     */

    private static function getImportObject($service, $sessID)
    {
        try {
            unset($_SESSION[$service]);
            switch ($service) {
                /**
                 * Здесь мы получаем массив элементов для выгрузки.
                 * Для этого следует написать отдельные методы, которые необходимы в конкретном случае.
                 *
                 * Наприммер массив данных сожно получать из CSV, Xml т т.д. Ниже приведены 2 простых примера.
                 */
                case "import_news": {
                    $importObject = self::getNews();
                    break;
                }

                case "import_catalog": {
                    $importObject = self::getProducts();
                    break;
                }
            }
            // запись в сессию
            $_SESSION[$service] = $importObject;
            $_SESSION[$service]["sessid"] = $sessID;
        } catch (\Exception $e) {

        }
        return $importObject;
    }

    /**
     * Импорт элемента
     *
     * @param mixed $xmlElement элемент для импорта
     * @param string $service символьный код сервиса выгрузки
     *
     */

    private static function importElement($importObject, $service)
    {
        switch ($service) {
            case "import_news": {
                self::importNew($importObject);
                break;
            }
            case "import_catalog": {
                self::importProduct($importObject);
                break;
            }
        }
    }


    /**
     * Функции, которые должны быть вызваны в конце импорта
     *
     * @param string $service - код сервиса
     *
     */
    private static function importEpilog($service)
    {

        switch ($service) {
            case 'import_news': {
                // Действия после импорта новостей. Например, удаление всех новостей, которых не было в выгрузке.
                break;
            }
            case 'import_catalog': {
                // Действия после импорта товаров
                break;
            }
        }
        unset($_SESSION['nodelete']);

    }


    /*---------------------------------------------------------------------------------------------------------------------*/

    /*
     * Внимание! Ниже приведены методы для визуальной демонстрации работы сервиса,
     * для реального проекта они должны быть вынесены в отдельные сущности классы.
     *
     * */

    /*---------------------------------------------------------------------------------------------------------------------*/


    /**
     * Импорт новости (тестовый пример)
     *
     * @param mixed $xmlElement элемент для импорта
     *
     */
    private static function importNew($importObject)
    {
        // Здесь реализуется логика добавления новости.
    }


    /**
     * Получение массива новостей для импорта (тестовый пример)
     *
     * @param void
     * @return array
     */
    private static function getNews()
    {
        // здесть вместо простого присвоения нужно написать получение данных.
        $importObject = array(
            array(
                "name" => "Товар 1",
                "props" => array(
                    "color" => "#ccc",
                    "price" => "500"
                )
            ),
            array(
                "name" => "Товар 2",
                "props" => array(
                    "color" => "#000",
                    "price" => "750"
                )
            ),
            array(
                "name" => "Товар 3",
                "props" => array(
                    "color" => "#fff",
                    "price" => "450"
                )
            ),
            array(
                "name" => "Товар 4",
                "props" => array(
                    "color" => "#f00",
                    "price" => "770"
                )
            )
        );
        return $importObject;
    }
    

    /**
     * Импорт товара (тестовый пример)
     *
     * @param mixed $xmlElement элемент для импорта
     *
     */
    private static function importProduct($importObject)
    {
        // Здесь реализуется логика добавления товара.
    }


}