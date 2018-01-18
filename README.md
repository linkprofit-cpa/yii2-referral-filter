# ReferralFilter
## Installation
`composer require linkprofit-cpa/yii2-referral-filter`
## Usage
Add code above to the controller behaviors section:

```php
/**
 * @inheritdoc
 */
public function behaviors()
{
    /* 1 day in seconds, until browser closed by default */
    $expire = 60 * 60 * 24;
    return [
        [
            'class' => 'linkprofit\ReferralFilter\ReferralFilter',
            'sessionMarkers' => [
                'data1', 'data2', 'data3', 'data4', 'data5', 'chan'
            ],
            'cookiesMarkers' => [
                'refid', 'cfads', 'CampaignID'
            ],
            'cookiesExpire' => $expire
        ],
    ];
```

Session markers are written to `$_SESSION['markers']['markerName']`, cookies markers are written directly to `$_COOKIE['markerName']`;