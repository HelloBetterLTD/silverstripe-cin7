<?php

namespace SilverStripers\Cin7\Connector\Loader;

use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBField;

class ImageLoader
{

    const FOLDER_PATH = 'Uploads/Cin7';

    private static function get_folder()
    {
        return Folder::find_or_make(self::FOLDER_PATH);
    }

    public static function load($url)
    {
        $hash = md5($url);
        $file = File::get()->find('ExternalHash', $hash);
        if (!$file || !$file->exists()) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_BINARYTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1
            ));
            $rawImage = curl_exec($curl);
            $resultInfo = curl_getinfo($curl);

            if ($resultInfo['http_code'] !== 200) {
                $file = null;
            } elseif ($rawImage) {
                $fileName = basename($url);
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileName = str_replace('.' . $extension, '', $fileName);
                if (!$extension) {
                    if ($resultInfo['content_type'] == 'image/jpeg') {
                        $extension = 'jpeg';
                    } elseif ($resultInfo['content_type'] == 'image/png') {
                        $extension = 'png';
                    } elseif ($resultInfo['content_type'] == 'image/gif') {
                        $extension = 'gif';
                    }
                }
                $fileClass = File::get_class_for_file_extension(
                    $extension
                );
                $fileObj = Injector::inst()->create($fileClass);
                $saveFileName = $fileName . '.' . $extension;

                $tmpFile = tmpfile();
                fwrite($tmpFile, $rawImage);
                fseek($tmpFile, 0);

                /* @var $assetStore AssetStore */
                $assetStore = Injector::inst()->get(AssetStore::class);

                if ($result = $assetStore->setFromStream($tmpFile, $saveFileName)) {
                    $dbFile = DBField::create_field('DBFile', $result);
                    if ($dbFile->exists()) {

                        $fileObj->update([
                            'Name' => $saveFileName,
                            'Title' => $saveFileName,
                            'File' => $dbFile,
                        ]);

                        $folder = self::get_folder();
                        if ($folder) {
                            $fileObj->ParentID = $folder->ID;
                        }
                        $fileObj->ExternalHash = $hash;
                        $fileObj->write();
                        $file = $fileObj;
                    }
                }
                if ($tmpFile) {
                    fclose($tmpFile);
                }
            }

        }
        return $file;
    }

}
