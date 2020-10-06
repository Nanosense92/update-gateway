<?php
$jsonfile = file_get_contents('/home/pi/Nano-Setting.json');
$json = json_decode($jsonfile, true);
?>
<!DOCTYPE html>
<html>
    <head>
        <title> Current Firmware configuration </title>
        <link rel="stylesheet" type="text/css" href="json-edit.css">
        <link rel="stylesheet" type="text/css" href="buttons.css">
        <script type="text/javascript" src="jquery-3.1.1.min.js"></script>
        <script type="text/javascript" src="json-edit.js"></script>
        <script type="text/javascript">
        var jsonArray = JSON.parse('<?php echo json_encode($json);?>');
        var jsonSchema = {
            "title": "json schema",
            "type": "object",
            "properties": {
                "versionField": {
                    "title": "Version",
                    "type": "integer",
                    "ui": {
                        "editor": "integer",
                        "inlineHint": "Last stable version"
                    }
                },
                "auto-updateField": {
                    "title": "Auto-update",
                    "type": "boolean",
                    "ui": {
                        "editor": "integer",
                        "inlineHint": "Enable/disable auto-update at 2:00 A.M. UTC"
                    }
                },
                "timezoneOffsetField": {
                    "title": "Timezone offset",
                    "type": "integer",
                    "minimum": -12,
                    "maximum": 14,
                    "ui": {
                        "editor": "integer",
                        "inlineHint": "Set the timezone offset of the region \
                            where you are (min = UTC-12, max = UTC+14)"
                    }
                },
                "sendDataIntervalField": {
                    "title": "Send data interval",
                    "type": "integer",
                    "minimum": 1,
                    "maximum": 60,
                    "ui": {
                        "editor": "integer",
                        "inlineHint": "Set the interval of time (in minutes) \
                            between 2 data sending (min = 1 minute, max = 60 minutes)"
                    }
                },
                "averageModeField": {
                    "title": "Average mode",
                    "type": "boolean",
                    "ui": {
                        "editor": "integer",
                            "inlineHint": "Enable/disable average mode: instead of sending all the data of the last\
                                'send data interval' minutes, send an average value of each data of each probe at the same interval"
                    }
                }
            }
        };

        var jsonValue = {
            "versionField": jsonArray["version"],
            "auto-updateField": jsonArray["auto-update"],
            "timezoneOffsetField": jsonArray["timezone"],
            "sendDataIntervalField": jsonArray["send-data-interval"],
            "averageModeField": jsonArray["average-mode"]
        };

        var myEditor;
        $(document).ready(function () {
            myEditor = $("#jsonEditor").jsonEdit({
                "schema": jsonSchema,
                "value": jsonValue
            });

            document.getElementById("auto-update").value = $("#jsonEditor_auto-updateField").prop('checked');
            document.getElementById("timezone-offset").value = $("#jsonEditor_timezoneOffsetField").val();
            document.getElementById("send-data-interval").value = $("#jsonEditor_sendDataIntervalField").val();
            document.getElementById("average-mode").value = $("#jsonEditor_averageModeField").prop('checked');

            $("#jsonEditor_versionField").replaceWith(function(){
                return '<p class="'+this.className+'">'+this.value+'</p>';
            });

            $("#jsonEditor_auto-updateField").change(function() {
                document.getElementById("auto-update").value = $("#jsonEditor_auto-updateField").prop('checked');
            });

            $("#jsonEditor_timezoneOffsetField").change(function() {
                document.getElementById("timezone-offset").value = $("#jsonEditor_timezoneOffsetField").val();
            });

            $("#jsonEditor_sendDataIntervalField").change(function() {
                document.getElementById("send-data-interval").value = $("#jsonEditor_sendDataIntervalField").val();
            });
            
            $("#jsonEditor_averageModeField").change(function() {
                document.getElementById("average-mode").value = $("#jsonEditor_averageModeField").prop('checked');
            });
            
            $("#jsonEditor_timezoneOffsetField").attr({
                "max" : 14,
                "min": -12
            });
            
            $("#jsonEditor_sendDataIntervalField").attr({
                "max" : 60,
                "min": 1
            });

        });
        </script>
        <meta charset="utf-8">
        <div>
            <center>
                <a href="main.php">
                    <img src="nano-header.png" width="322" height="63">
                </a>
            </center>
        </div>
    </head>
    <body>
        <h1> Current Firmware configuration </h1>
        <form method="post" action="modify_config.php">
            <div id="jsonEditor"></div>
            <input type="hidden" id="auto-update" name="auto-update" value="">
            <input type="hidden" id="timezone-offset" name="timezone-offset" value="">
            <input type="hidden" id="send-data-interval" name="send-data-interval" value="">
            <input type="hidden" id="average-mode" name="average-mode" value="">
            <div class="clearfix">
                <button onclick="window.location.href='main.php'" type="button" class="cancelbtn">Cancel</button>
                <button type="submit" class="signupbtn" name="save_config">Save</button>
            </div>
        </form>
    </body>
</html>
