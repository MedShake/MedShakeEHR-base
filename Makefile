default:
	zip -r MedShakeEHR-base.zip . -x .git\* -x Makefile -x installer\* -x tools\*

clean:
	rm -f MedShakeEHR-base.zip