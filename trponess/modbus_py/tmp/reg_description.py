#!/usr/bin/python3
# -*- coding: utf-8 -*-

#register @0 E4000
slave_status_e4000 = {
    0: "Pre-heating",
    1: "OK",
    2: "Temperature fault",
    3: "Sensor fault",
    4: "Power supply fault",
    5: "EEPROM fault",
    6: "Not calibrated",
    7: "Calibrating",
}

#register @0 P4000
slave_status_p4000 = {
    0: "OK",
    1: "Optic needs a cleaning",
    2: "Low voltage",
    3: "Saturation",
    6: "Pre_heating",
    7: "Sensor OFF",
}

#register @1 E4000/P4000
gas = {
    0: "CO",
    1: "O2",
    2: "O3",
    3: "H2",
    4: "CH4",
    5: "Particules",
    6: "Radon",
    7: "H2S",
    10: "NO2",
    14: "EC",
    15: "CO2",
}

#register @6 E4000
speed_status = {
    0: "OFF",
    255: "ON",
}

#register @7 E4000
speed_threshold = {
    0: "Level 1",
    255: "Level 2",
}

#register @8 E4000
dry_contact_type = {
    0: "Normally open (NO)",
    255: "Normally close (NC)",
}

#register @9 P4000
sensor_command = {
    65280: "ON",
    255: "OFF",
}

#pretty print for the different values of the registers of the E4000
def pretty_print_e4000(reg):
    print("Slave status: " + slave_status_e4000[reg[0]])
    print("GAS: " + gas[reg[1]])
    print("CO2 concentration: " + str(reg[2]) + " ppm")
    print("VOC concentration: " + str(reg[3]*10) + " µg/m3")
    print("Temperature: " + str(reg[4]/10) + " °C")
    print("Humidity: " + str(reg[5]) + " %")
    print("ON/OFF speed command status:")
    print("     Speed 1: " + speed_status[int("0x" + "{:04x}".format(reg[6])[:2], 0)] +
            " / Speed 2: " + speed_status[int("0x" + "{:04x}".format(reg[6])[2:], 0)])
    print("ON/OFF air conditioning command threshold:")
    print("     Speed 1: " + speed_threshold[int("0x" + "{:04x}".format(reg[7])[:2], 0)] +
            " / Speed 2: " + speed_threshold[int("0x" + "{:04x}".format(reg[7])[2:], 0)])
    print("Dry contacts types:")
    print("     Speed 1: " + dry_contact_type[int("0x" + "{:04x}".format(reg[8])[:2], 0)] +
            " / Speed 2: " + dry_contact_type[int("0x" + "{:04x}".format(reg[8])[2:], 0)])
    print("Linear ventilation command: " + str(reg[9]) + " %")
    print("Heating command value: " + str(reg[10]) + " %")
    print("Air conditioning command value: " + str(reg[11]) + " %")
    print("Heating setpoint setting: " + str(reg[12]/10) + " °C")
    print("Air conditioning setpoint setting: " + str(reg[13]/10) + " °C")
    print("Firmware version: " + str(reg[14]))
    print("")

#pretty print for the different values of the registers of the E4000
def pretty_print_p4000(reg):
    print("Slave status: " + slave_status_p4000[reg[0]])
    print("GAS: " + gas[reg[1]])
    print("PM 1 µm weight: " + str(reg[2]) + " µg/m3")
    print("PM 2.5 µm weight: " + str(reg[3]) + " µg/m3")
    print("PM 10 µm weight: " + str(reg[4]) + " µg/m3")
    print("PM 1 number: " + str(reg[5]) + " per m3")
    print("PM 2.5 number: " + str(reg[6]) + " per m3")
    print("PM 10 number: " + str(reg[7]) + " per m3")
    print("Firmware version: " + str(reg[8]))
    if (reg[8] >= 108):
        print("ON/OFF sensor command: " + sensor_command[reg[9]])
    print("")
