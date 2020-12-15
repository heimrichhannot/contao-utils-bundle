# Upgrade notices

## Prepare your bundle for utils bundle v3

Following steps could be done to prepare your bundle for utils bundle version 3 and make a smooth transition supporting v2 and v3.

* Bundle class renamed
    - HeimrichHannotContaoUtilsBundle renamed to HeimrichHannotUtilsBundle
    - min. bundle version: 2.187.0
* Bundle alias renamed 
    - alias renamed from utils_bundle to huh_utils
    - change configuration key utils_bundle to huh_utils
    - min. bundle version: 2.187.0
* Loading assets disabled by default
    - default value for huh_utils.enable_load_assets changed from true to false
    - set to true in your configuration to ensure assets are still added in v3
    