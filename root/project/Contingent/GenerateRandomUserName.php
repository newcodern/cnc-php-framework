<?php
namespace Contingent;
class GenerateRandomUserName {
    private $japaneseFirstNames = ['Haruto', 'Yuto', 'Sora', 'Hinata', 'Ren', 'Yuki', 'Hana', 'Akari', 'Sakura', 'Riko'];
    private $russianFirstNames = ['Alexander', 'Dmitry', 'Ivan', 'Vladimir', 'Sergei', 'Nikolai', 'Pavel', 'Anastasia', 'Yelena', 'Ekaterina'];
    private $japaneseLastNames = ['Sato', 'Suzuki', 'Takahashi', 'Tanaka', 'Watanabe', 'Ito', 'Yamamoto', 'Nakamura', 'Kobayashi', 'Kato'];
    private $russianLastNames = ['Ivanov', 'Smirnov', 'Popov', 'Kuznetsov', 'Vasilyev', 'Petrov', 'Sokolov', 'Mikhailov', 'Fedorov', 'Morozov'];


    public function generateRandomJapaneseRussianName() {

        $order = rand(0, 1);
        $japaneseFirstName = $this->japaneseFirstNames[array_rand($this->japaneseFirstNames)];
        $russianFirstName = $this->russianFirstNames[array_rand($this->russianFirstNames)];
        $japaneseLastName = $this->japaneseLastNames[array_rand($this->japaneseLastNames)];
        $russianLastName = $this->russianLastNames[array_rand($this->russianLastNames)];
        if ($order == 0) {
            $username = $japaneseFirstName . '_' . $russianLastName. rand() . time();
        } else {
            $username = $russianFirstName . '_' . $japaneseLastName. rand() . time();
        }

        return $username;
    }
}