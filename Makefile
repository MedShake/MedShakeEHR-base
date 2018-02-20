default:
	zip -r MedShakeEHR-base.zip . -x .git\* -x Makefile

clean:
	rm -f MedShakeEHR-base.zip
