#!/bin/bash -x

PID=$(pgrep -u ${USER} -f bin/gnome-shell)
kill -9 ${PID}
sleep 5
export DISPLAY=:${USER##xdev}
source ~/test_app/scripts/CommandCenter.log
#Set Incomming Request Arguments
VPN_PROVIDER=$1
VPN_REGION=$2
SIM_PROVIDER=$3
SIM_REGION=$4
ACTIVATION_TYPE=$5
EMULATOR_ALPHA=$6

HOSTNAME=$(hostname)
USERNAMESHORT=${USER}-${HOSTNAME}
USERNAMEFULL=${USER}-${HOSTNAME}-${EMULATOR_ALPHA}
SABYPATH=/${HOME}/test_app
REMOTE_SERVER=http://95.217.198.229/apk/MOH_AVD

#Analyze Get Emulator ID from the ALPHA
case ${EMULATOR_ALPHA} in "A") EMULATOR_ID="" ;;
"B") EMULATOR_ID=1 ;;
"C") EMULATOR_ID=2 ;;
"D") EMULATOR_ID=3 ;;
"E") EMULATOR_ID=4 ;;
"F") EMULATOR_ID=5 ;;
"G") EMULATOR_ID=6 ;;
"H") EMULATOR_ID=7 ;;
"I") EMULATOR_ID=8 ;;
"J") EMULATOR_ID=9 ;;
esac

ENV_JSON=$(curl -s ${COMMANDCENTERURL}/getConfigEnvironment/${USERNAMESHORT})
ENV_HOST_API=$(echo $ENV_JSON | python2 -c "import sys, json; print json.load(sys.stdin)['host_api']")
ENV_IP=$(echo $ENV_JSON | python2 -c "import sys, json; print json.load(sys.stdin)['host_beanstalk']")

PROFILE_JSON=$(curl -s http://${ENV_HOST_API}/getSabyProfileDetail/${USERNAMEFULL})
PROFILE_WA_TYPE=$(echo $PROFILE_JSON | python2 -c "import sys, json; print json.load(sys.stdin)['whatsapp_type']")
PROFILE_APK_NAME=$(echo $PROFILE_JSON | python2 -c "import sys, json; print json.load(sys.stdin)['apk_name']")
PROFILE_AVD_NAME=$(echo $PROFILE_JSON | python2 -c "import sys, json; print json.load(sys.stdin)['avd_name']")
PROFILE_AVD_TAG="default"
PROFILE_SETTINGS_TYPE="29"

# get Emulator Port Per Xdev
ADB_PORTS=$(curl -s http://${ENV_HOST_API}/getSabyAdbPort/${USER})
CONSOLE_PORT=$(echo $ADB_PORTS | python2 -c "import sys, json; print json.load(sys.stdin)['console_port']")
ADB_PORT=$(echo $ADB_PORTS | python2 -c "import sys, json; print json.load(sys.stdin)['adb_port']")

# check if the Main is already running
function mainProcess() {
    pgrep -u ${USER} main_new
    MAINPROCESS=$?
    return $MAINPROCESS
}

mainProcess
MAINPROCESS=$?
if [[ "$MAINPROCESS" -eq 0 ]]; then
    exit 1
fi

curl -s http://${ENV_HOST_API}/setSabyEmulatorOffline/${USERNAMESHORT}
curl -s http://${ENV_HOST_API}/setSabyEmulatorOnline/${USERNAMEFULL}
curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/PRE+ACTIVATING/CREATING

logger "kill android.."
pkill -u ${USER} -9 qemu

#Set Of Donwload Links
REMOTE_DEVICES=${REMOTE_SERVER}/devices.xml
#Set Of local Directories and NEW AVD Name
AVD_HOME=/home/${USER}/.android
AVD_NAME="GB"
#Set Of local FILES
LOCAL_DEVICES=${AVD_HOME}/devices.xml
LOCAL_INI=${AVD_HOME}/avd/${AVD_NAME}.ini

if [[ -f /home/${USER}/Android/Sdk/cmdline-tools/latest/bin/avdmanager ]]; then
    # Delete previous AVD
    ~/Android/Sdk/cmdline-tools/latest/bin/avdmanager delete avd -n ${AVD_NAME}
    # Clean .android directory
    rm -rf ${AVD_HOME}/avd/${AVD_NAME}.avd
    rm -rf ${LOCAL_INI}
    #downloading New Files (devices.xml)
    curl -o ${LOCAL_DEVICES} ${REMOTE_DEVICES}
    #Install updates if available
    #yes Y | ~/Android/Sdk/cmdline-tools/latest/bin/sdkmanager --update
    # Install android image with sending "yes" as args
    #yes Y | ~/Android/Sdk/cmdline-tools/latest/bin/sdkmanager --update
    if [[ -d "/home/${USER}/Android/Sdk/system-images/android-29/default" ]]; then
        logger "THE PROFILE ANDROID IS EXISET ...."
    else
        yes Y | ~/Android/Sdk/cmdline-tools/latest/bin/sdkmanager --install "system-images;android-${PROFILE_SETTINGS_TYPE};${PROFILE_AVD_TAG};x86_64"
    fi

    logger "INSTALL BUILD-TOOLS"
    yes Y | ~/Android/Sdk/cmdline-tools/latest/bin/sdkmanager --install "build-tools;34.0.0-rc1"
    # Create AVD using the downloaded image
    SDK_VERSION=$(/home/${USER}/Android/Sdk/cmdline-tools/latest/bin/sdkmanager --version)
    if [ ${SDK_VERSION} == "8.0" ] || [ ${SDK_VERSION} == "9.0" ]; then
        ~/Android/Sdk/cmdline-tools/latest/bin/avdmanager --verbose create avd --force --name "${AVD_NAME}" --package "system-images;android-${PROFILE_SETTINGS_TYPE};${PROFILE_AVD_TAG};x86_64" --tag "${PROFILE_AVD_TAG}" --abi "x86_64" -d "Custom" -c 1G --skin nexus_5
    else
        ~/Android/Sdk/cmdline-tools/latest/bin/avdmanager --verbose create avd --force --name "${AVD_NAME}" --package "system-images;android-${PROFILE_SETTINGS_TYPE};${PROFILE_AVD_TAG};x86_64" --tag "${PROFILE_AVD_TAG}" --abi "x86_64" -d "Custom" -c 1G
    fi
    # Sleep for 10 sec to give it time for booting up
    sleep 10
    # Replacing config strings to update the configration file
    sed -i "s/hw.keyboard=no/hw.keyboard=yes/g" ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini
    sleep 1
    sed -i "s/runtime.network.speed=Full/runtime.network.speed=full/g" ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini
    sleep 1
    #sed -i "s/hw.gpu.mode=auto/hw.gpu.mode=guest/g" ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini
    #sleep 1
    sed -i "s/runtime.network.latency=None/runtime.network.latency=none/g" ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini
    sleep 1
#    sed -i "s/skin.path=_no_skin/skin.dynamic=yes\\nskin.name=nexus_5\\nskin.path=\\/home\\/${USER}\\/Android\\/Sdk\\/skins\\/nexus_5/g" ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini
    echo -e "skin.dynamic=yes\nskin.name=nexus_5\nskin.path=/home/${USER}/Android/Sdk/skins/nexus_5" >> ${AVD_HOME}/avd/${AVD_NAME}.avd/config.ini

    sleep 1
fi

logger "CREATE SCRIPT COMPLETED..."
logger "WAITING TO CHECK IN ACTIVATING STATUS"
curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/PRE+ACTIVATING/WAITING+START+AVD
CAN_STARTING=$(curl -s http://${ENV_HOST_API}/ServerSabyCheckInState/${HOSTNAME}/ACTIVATING)
while [ "$CAN_STARTING" == "false" ]; do
    sleep 60
    CAN_STARTING=$(curl -s http://${ENV_HOST_API}/ServerSabyCheckInState/${HOSTNAME}/ACTIVATING)
done
curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/ACTIVATING/WIPING
function emulatorWipeStart() {
    nohup ~/Android/Sdk/emulator/emulator @${AVD_NAME} -port 5544 -no-snapshot -camera-back none -camera-front none -memory 2048 -cache-size 1000 -partition-size 8192 -shell -qemu -allow-host-audio >~/avd.log 2>&1 &
}
emulatorWipeStart
sleep 10
RUNNING_EMULATOR_DETAILS=$(~/7eet-saby-whatsapp-ubuntu20/scripts/get_avd_port_name.sh)

COUNT=0
while [ "$RUNNING_EMULATOR_DETAILS" == "0" ] && [ "$COUNT" -lt 3 ]; do
    emulatorWipeStart
    sleep 5
    RUNNING_EMULATOR_DETAILS=$(~/7eet-saby-whatsapp-ubuntu20/scripts/get_avd_port_name.sh)
    ((COUNT++))
done

if [[ "$COUNT" -eq 3 ]]; then
    curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/KILLED/NO+AVD
    exit 1
fi

RUNNING_EMULATOR_NAME="$(cut -d',' -f1 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_PORT="$(cut -d',' -f2 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_X="$(cut -d',' -f3 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_Y="$(cut -d',' -f4 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_WIDTH="$(cut -d',' -f5 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_HEIGHT="$(cut -d',' -f6 <<<${RUNNING_EMULATOR_DETAILS})"
RUNNING_EMULATOR_ID=${RUNNING_EMULATOR_NAME: -1}
if [ "$RUNNING_EMULATOR_ID" == "B" ]; then
    RUNNING_EMULATOR_ID=""
fi

if [ "$RUNNING_EMULATOR_ID" != "$EMULATOR_ID" ]; then
    pkill -9 -u ${USER} "qemu*"
    rm -f ${HOME}/.android/avd/$RUNNING_EMULATOR_NAME.avd/*lock
    echo "" >~/avd.log
    emulatorWipeStart
    sleep 5
fi

function emulatorProcess() {
    pgrep -u ${USER} qemu
    PROCESS=$?
    return $PROCESS
}

emulatorProcess
PROCESS=$?
COUNT=0
while [ "$PROCESS" -eq 1 ] && [ "$COUNT" -lt 3 ]; do
    emulatorWipeStart
    sleep 5
    emulatorProcess
    PROCESS=$?
    ((COUNT++))
done

if [ "$PROCESS" -eq 1 ] && [ "$COUNT" -eq 3 ]; then
    curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/KILLED/NO+AVD
    exit 1
fi

if [ "$EMULATOR_ID" == "" ]; then
    EMULATOR_ID="0"
fi

curl -s http://${ENV_HOST_API}/setSabyEmulatorState/${USERNAMEFULL}/PRE+ACTIVATING/WAITING+START+PROCCESS


#logger "STARTING ACTIVATION."
$SABYPATH/saby/activate_new.php WIPE $ENV_HOST_API $PROFILE_WA_TYPE $PROFILE_SETTINGS_TYPE $PROFILE_AVD_NAME $VPN_PROVIDER $VPN_REGION $SIM_PROVIDER $SIM_REGION $ACTIVATION_TYPE $EMULATOR_ALPHA $EMULATOR_ID $RUNNING_EMULATOR_PORT $RUNNING_EMULATOR_X $RUNNING_EMULATOR_Y $RUNNING_EMULATOR_WIDTH $RUNNING_EMULATOR_HEIGHT $ENV_IP &
