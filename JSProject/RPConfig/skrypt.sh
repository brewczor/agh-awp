while :
do
	#Time
	TIME=$(date +"%Y%m%d%H%M%S")
	echo $TIME

	#Processor usage
	PROC=$(top -b -n 5 -d.2 | grep "Cpu" | tail -n1 | awk '{print($2)}' | cut -d'%' -f 1)
	echo "Proc:  $PROC"

	#Memory
	MEMTOT=$(cat /proc/meminfo | grep "MemTotal" | awk '{print $2}')
	MEMFREE=$(cat /proc/meminfo | grep "MemFree" | awk '{print $2}')

	MEMUSE=$((MEMTOT-MEMFREE))

	MEM=`awk 'BEGIN{printf("%0.2f", '$MEMUSE' / '$MEMTOT' * 100)}'`
	echo "Mem: $MEM"
	
	echo http://192.168.0.30:5984/rpdata/${TIME}

	curl -X PUT http://192.168.0.30:5984/rpdata/${TIME} -H "Content-Type: application/json" -d '{"type":"rpdata","Time":"'"$TIME"'", "Memory":"'"$MEM"'", "CPU":"'"$PROC"'"}'

	sleep 1
done
