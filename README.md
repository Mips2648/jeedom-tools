# jeedom-tools

Tools and helper class for Jeedom plugin development

[![Tests PHP 7.4](https://github.com/Mips2648/jeedom-tools/actions/workflows/ci.yml/badge.svg)](https://github.com/Mips2648/jeedom-tools/actions/workflows/ci.yml)

## How to use it

The most simple and cleanest way is to use composer:
`composer require mips/jeedom-tools`

And then to add the autoloader in your eqLogic file, example:

```PHP
require_once __DIR__ . '/../../vendor/autoload.php';

class pluginTemplate extends eqLogic {
    use MipsEqLogicTrait;
```

Using composer will make easier to upgrade the library if needed (`composer u`)

Alternatively you can add the source as you wish to your plugin, add the `require` statement and make sure to use the trait.

## Running eqLogic function asynchronously

To run a function asynchronously we will use the cron system of Jeedom.

First you need to create a method with this signature that will do the work you want:

```PHP
public static function myMethodAsync($_options) {

}
```

`$_options` is a array of value that you will use to pass arguments to your method, exactly like cron tasks.

To execute your method you need to call the `executeAsync` method:

```PHP
self::executeAsync('myMethodAsync', array(
    'param1' => 'value1',
    'param2' => 'value2'
));
```

The static function `executeAsync` will simply create a new oneTime cron and run it.
There is a third argument `$_date`, which is by default equal to `now`, to which you can pass any English textual datetime description that `strtotime()` can interpret.

## Creating eqLogic commands

The concept is to define commands to use in a json file, with a defined structure then call the method that will create all corresponding commands.
I suggest to put this config file in the following folder of your plugin: `/core/config/` and the code snippet below will assume that.

I know it is possible import a json file directly to create commands config put the following give more flexibility and control like ability to assign values only at creation (template & display section), link action & info commands...

The code that actually creates the command has been widely inspired from the one that we can found in some official plugins: I've added some features, made it more generic and put in place a way to share it between all my plugins (this repo) (and maybe yours ;-))

A generic example of json file, below you will see more details information:

```json
{
    "node": [
        {
            "logicalId": "refresh",
            "name": "Rafraichir",
            "type": "action",
            "subtype": "other",
            "isVisible": 1
        },
        {
            "logicalId": "cpu",
            "name": "CPU",
            "type": "info",
            "subtype": "numeric",
            "unite": "%",
            "isVisible": 1,
            "isHistorized": 1,
            "configuration": {
                "minValue" : 0,
                "maxValue" : 100
            }
        },
        {
            "logicalId": "maxcpu",
            "name": "Nombre de CPU",
            "type": "info",
            "subtype": "numeric",
            "isVisible": 0,
            "isHistorized": 0,
            "template": {
                "dashboard" : "line",
                "mobile" : "line"
            }
        },
        {
            "logicalId": "start",
            "name": "Démarrer",
            "type": "action",
            "subtype": "other",
            "isVisible": 1,
            "display": {
                "icon": "<i class=\"fas fa-play\"><\/i>"
            }
        },
        {
            "logicalId": "pause",
            "name": "Pause",
            "type": "action",
            "subtype": "other",
            "isVisible": 1,
            "display": {
                "icon": "<i class=\"fas fa-pause\"><\/i>"
            }
        }
  ],
  "lamp": [
        {
            "logicalId": "nightLightOn",
            "name": "Veilleuse On",
            "type": "action",
            "subtype": "other",
            "generic_type": "LIGHT_ON",
            "value": "nightLightState",
            "template": {
                "dashboard" : "light",
                "mobile" : "light"
            }
        },
        {
            "logicalId": "nightLightOff",
            "name": "Veilleuse Off",
            "type": "action",
            "subtype": "other",
            "generic_type": "LIGHT_OFF",
            "value": "nightLightState",
            "template": {
                "dashboard" : "light",
                "mobile" : "light"
            }
        },
        {
            "logicalId": "nightLightState",
            "name": "Veilleuse",
            "type": "info",
            "subtype": "binary",
            "generic_type": "LIGHT_STATE",
            "isVisible": 0,
            "isHistorized": 1,
            "initialValue": 0
        }
  ]
}
```

As you see you can have different set of commands in your file, `node`and `lamp` in the example, and they can be created with following line of code:

```PHP
$eqLogic->createCommandsFromConfigFile(__DIR__ . '/../config/commands.json', 'lamp');
```

Below you will find several concrete case. I create commands of my most complexe plugins using this method, it means that I've encountered a lot of use cases already and the current version should cover all your needs so if you don't achieve something or if something is unclear, you probably know how to find me ;-)

### A state information and 2 actions on & off

You can see in the example below how to assign generic type, default template, command type & subtype.

Please note that the template section is applied on the command only during creation, if the command already exists the function will not replace it to not override a change done by a user of the plugin.

- `value` is used on an action command to link the corresponding info command; the order is not important, the function will take care to link both after having created both.
- `initialValue` is used to assign an initial value to an info command, it might not be needed in this particular example but they are others cases when this is very handy ;-)

```json
        {
            "logicalId": "nightLightOn",
            "name": "Veilleuse On",
            "type": "action",
            "subtype": "other",
            "generic_type": "LIGHT_ON",
            "value": "nightLightState",
            "template": {
                "dashboard" : "light",
                "mobile" : "light"
            }
        },
        {
            "logicalId": "nightLightOff",
            "name": "Veilleuse Off",
            "type": "action",
            "subtype": "other",
            "generic_type": "LIGHT_OFF",
            "value": "nightLightState",
            "template": {
                "dashboard" : "light",
                "mobile" : "light"
            }
        },
        {
            "logicalId": "nightLightState",
            "name": "Veilleuse",
            "type": "info",
            "subtype": "binary",
            "generic_type": "LIGHT_STATE",
            "isVisible": 0,
            "isHistorized": 1,
            "initialValue": 0
        }
```

### A slider action command

The example is self-explanatory

```json
        {
            "logicalId": "nightLightBrightness",
            "name": "Etat luminosité",
            "type": "info",
            "subtype": "numeric",
            "generic_type": "LIGHT_BRIGHTNESS",
            "isVisible": 0,
            "isHistorized": 0,
            "initialValue": 0
        },
        {
            "logicalId": "setNightLightBrightness",
            "name": "Luminosité veilleuse",
            "type": "action",
            "subtype": "slider",
            "configuration": {
                "value" : "#slider#",
                "minValue" : 0,
                "maxValue" : 255
            },
            "generic_type": "LIGHT_SLIDER",
            "isVisible": 1,
            "value": "nightLightBrightness"
        }
```

### A select action command (list)

```json
        {
            "logicalId": "nightLightMode",
            "name": "Etat mode veilleuse",
            "type": "info",
            "subtype": "string",
            "isVisible": 0,
            "isHistorized": 0
        },
        {
            "logicalId": "setNightLightMode",
            "name": "Mode veilleuse",
            "type": "action",
            "subtype": "select",
            "configuration": {
                "listValue": "rgb|Couleur;temperature|Blanc;rainbow|Jeu de lumière"
            },
            "isVisible": 1,
            "value": "nightLightMode"
        }
```

### A color action command

```json
{
            "logicalId": "nightLightColor",
            "name": "Etat couleur veilleuse",
            "type": "info",
            "subtype": "string",
            "generic_type": "LIGHT_COLOR",
            "isVisible": 0,
            "isHistorized": 0
        },
        {
            "logicalId": "setNightLightColor",
            "name": "Couleur veilleuse",
            "type": "action",
            "subtype": "color",
            "generic_type": "LIGHT_SET_COLOR",
            "isVisible": 1,
            "value": "nightLightColor"
        }
```

### A message action command

The property in the display section are the one available within Jeedom:

- `title_placeholder`: the custom label of title zone (use in scenario e.g.)
- `message_placeholder`: the custom label of message zone (use in scenario e.g.)
- `title_disable` : 0 if title should be disabled (use in scenario e.g.)
- `message_disable` : 0 if message should be disabled (use in scenario e.g.)
- `message_cmd_type`: "info" or "action", if the zone must be a jeedom command, putting this will automatically create the command selector in scenario
- `message_cmd_subtype`: "numeric" (e.g.)
- `icon`: for example: `<i class=\"fas fa-clock\"><\/i>` to assign a icon to the command (could be used on any type of command, not only message.

```json
        {
            "logicalId": "sendSnapshot",
            "name": "Envoyer une capture",
            "type": "action",
            "subtype": "message",
            "generic_type": "",
            "isVisible": 0,
            "display": {
                "title_placeholder": "Texte (optionnel)",
                "message_placeholder": "Commande d'envoi de la capture",
                "message_cmd_type": "action",
                "message_cmd_subtype": "message"
            }
        }
```
