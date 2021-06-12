default:
	rm -f MedShakeEHR-base.zip SHA256SUMS
	zip -r MedShakeEHR-base.zip . -x .git\* -x Makefile -x installer\*
	sha256sum -b MedShakeEHR-base.zip > preSHA256SUMS
	head -c 64 preSHA256SUMS > SHA256SUMS
	rm -f preSHA256SUMS

clean:
	rm -f MedShakeEHR-base.zip
