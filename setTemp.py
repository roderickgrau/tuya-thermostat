#!/usr/bin/python3
#
# 04/24/2023
#

false = False
true = True

#sample data
data = {
  "8": false,
  "1": true,
  "27": -2,		#temp variance
  "3": "Warming",       #status
  "43": "in",
  "25": "close",
  "10": false,
  "29": 58,		#current temp
  "40": false,
  "17": 45,		#set temp
  "2": "manual",	#mode
  "102": 95,
  "101": 2,
  "18": 95
}

import sys
import json
import tinytuya

def main():
  home='/thermostat/'

  if ( len(sys.argv) > 1 ):
    action = sys.argv[1]
  else:
    print("Missing arguments.\n")
    quit()
  
  if ( len(sys.argv) > 2 ):
    id = sys.argv[2]
  else:
    print("Missing device ID.\n")
    quit()

  if ( action == 'set' ):
    dps = sys.argv[3]
    value = sys.argv[4]
  elif ( action == 'get' ):
    dps = sys.argv[3]
    value = 0
  elif ( action == 'show' ):
    dps = 0
    value = 0
  else:
    print("Usage: action:[set/get/show] deviceId dps value\n")
    quit()

  #get api key for cloud devices
  #with open(home + 'tinytuya.json') as f:
  #  api=json.load(f)
  #  f.close

  with open(home + 'devices.json') as f:
    devices = json.load(f)
    f.close()

  for device in devices:
    #print( json.dumps(device, indent=2) )
    d = tinytuya.OutletDevice(
      dev_id=device['id'],
      address=device['ip'],
      local_key=device['key'],
      version=3.3)

    if ( device['id'] == id ):
      if ( action == 'set' ):
        if ( dps == '17' ):
          print(json.dumps({"message":"Setting temp to " + value}) )
        else:
          print(json.dumps({"message":"Setting " + dps + " to " + value}) )
        d.set_value( str(dps), int(value) )
      elif ( action == 'get' ):
        items = d.status()
        output = {}
        output["dps"] = dps
        output["value"] = items['dps'][dps]
        print( json.dumps(output) )
      elif ( action == 'show' ):
        print( json.dumps(d.detect_available_dps(), indent=2) )
      else:
        print(json.dumps({"message":"Invalid action."}) )

  quit()

if __name__ == '__main__':
  main()
