default:
	zip -r MedShakeEHR-base.zip . -x .git\* -x Makefile -x installer\*

clean:
	rm -f MedShakeEHR-base.zip
